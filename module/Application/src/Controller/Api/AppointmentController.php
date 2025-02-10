<?php

declare(strict_types=1);

namespace Application\Controller\Api;

use Application\Service\AppointmentService;
use Application\Service\UserService;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractRestfulController;
use Laminas\View\Model\JsonModel;

class AppointmentController extends AbstractRestfulController
{
    private AppointmentService $appointmentService;
    private UserService $userService;

    public function __construct(
        AppointmentService $appointmentService,
        UserService $userService
    ) {
        $this->appointmentService = $appointmentService;
        $this->userService = $userService;
    }

    public function create($data)
    {
        try {
            $user = $this->getCurrentUser();
            if (!$user) {
                $this->getResponse()->setStatusCode(Response::STATUS_CODE_401);
                return new JsonModel([
                    'success' => false,
                    'message' => 'Usuário não autenticado',
                ]);
            }

            $this->validateAppointmentData($data);
            $appointment = $this->appointmentService->createAppointment($data, $user);
            
            return new JsonModel([
                'success' => true,
                'data' => $appointment->toArray(),
            ]);
        } catch (\Exception $e) {
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_400);
            return new JsonModel([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function get($id)
    {
        try {
            $user = $this->getCurrentUser();
            if (!$user) {
                $this->getResponse()->setStatusCode(Response::STATUS_CODE_401);
                return new JsonModel([
                    'success' => false,
                    'message' => 'Usuário não autenticado',
                ]);
            }

            $appointment = $this->appointmentService->getAppointment((int) $id);
            if (!$appointment) {
                $this->getResponse()->setStatusCode(Response::STATUS_CODE_404);
                return new JsonModel([
                    'success' => false,
                    'message' => 'Agendamento não encontrado',
                ]);
            }

            // Verifica se o usuário tem permissão para ver este agendamento
            if ($user->getRole() !== 'admin' && $appointment->getUser()->getId() !== $user->getId()) {
                $this->getResponse()->setStatusCode(Response::STATUS_CODE_403);
                return new JsonModel([
                    'success' => false,
                    'message' => 'Você não tem permissão para ver este agendamento',
                ]);
            }

            return new JsonModel([
                'success' => true,
                'data' => $appointment->toArray(),
            ]);
        } catch (\Exception $e) {
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_400);
            return new JsonModel([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function update($id, $data)
    {
        try {
            $user = $this->getCurrentUser();
            if (!$user) {
                $this->getResponse()->setStatusCode(Response::STATUS_CODE_401);
                return new JsonModel([
                    'success' => false,
                    'message' => 'Usuário não autenticado',
                ]);
            }

            $appointment = $this->appointmentService->updateAppointment((int) $id, $data, $user);
            if (!$appointment) {
                $this->getResponse()->setStatusCode(Response::STATUS_CODE_404);
                return new JsonModel([
                    'success' => false,
                    'message' => 'Agendamento não encontrado',
                ]);
            }

            return new JsonModel([
                'success' => true,
                'data' => $appointment->toArray(),
            ]);
        } catch (\Exception $e) {
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_400);
            return new JsonModel([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $user = $this->getCurrentUser();
            if (!$user) {
                $this->getResponse()->setStatusCode(Response::STATUS_CODE_401);
                return new JsonModel([
                    'success' => false,
                    'message' => 'Usuário não autenticado',
                ]);
            }

            $result = $this->appointmentService->deleteAppointment((int) $id, $user);
            if (!$result) {
                $this->getResponse()->setStatusCode(Response::STATUS_CODE_404);
                return new JsonModel([
                    'success' => false,
                    'message' => 'Agendamento não encontrado',
                ]);
            }

            return new JsonModel([
                'success' => true,
                'message' => 'Agendamento cancelado com sucesso',
            ]);
        } catch (\Exception $e) {
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_400);
            return new JsonModel([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getList()
    {
        try {
            $user = $this->getCurrentUser();
            if (!$user) {
                $this->getResponse()->setStatusCode(Response::STATUS_CODE_401);
                return new JsonModel([
                    'success' => false,
                    'message' => 'Usuário não autenticado',
                ]);
            }

            $showAll = $this->params()->fromQuery('show_all', false);
            $status = $this->params()->fromQuery('status');

            $criteria = [];
            if ($status) {
                $criteria['status'] = $status;
            }

            $appointments = $showAll ? 
                $this->appointmentService->listAppointments($criteria, $user) :
                $this->appointmentService->getUpcomingAppointments($user);

            return new JsonModel([
                'success' => true,
                'data' => array_map(fn($appointment) => $appointment->toArray(), $appointments),
            ]);
        } catch (\Exception $e) {
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_400);
            return new JsonModel([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function validateAppointmentData(array $data): void
    {
        $requiredFields = ['service_id', 'appointment_date'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new \InvalidArgumentException(
                'Os seguintes campos são obrigatórios: ' . implode(', ', $missingFields)
            );
        }

        if (!strtotime($data['appointment_date'])) {
            throw new \InvalidArgumentException('Data de agendamento inválida');
        }
    }

    private function getCurrentUser()
    {
        return $this->userService->getCurrentUser();
    }
} 