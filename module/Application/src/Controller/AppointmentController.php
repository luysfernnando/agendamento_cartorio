<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Form\AppointmentForm;
use Application\Service\AppointmentService;
use Application\Service\ServiceService;
use Application\Service\UserService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class AppointmentController extends AbstractActionController
{
    private AppointmentService $appointmentService;
    private ServiceService $serviceService;
    private UserService $userService;

    public function __construct(
        AppointmentService $appointmentService,
        ServiceService $serviceService,
        UserService $userService
    ) {
        $this->appointmentService = $appointmentService;
        $this->serviceService = $serviceService;
        $this->userService = $userService;
    }

    public function indexAction()
    {
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return $this->redirect()->toRoute('login');
        }

        $appointments = $this->appointmentService->listAppointments([], $user);

        return new ViewModel([
            'appointments' => $appointments,
        ]);
    }

    public function scheduleAction()
    {
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return $this->redirect()->toRoute('login');
        }

        $serviceId = $this->params()->fromRoute('service');
        $service = null;
        
        if ($serviceId) {
            $service = $this->serviceService->getService((int) $serviceId);
            if (!$service || !$service->isActive()) {
                $this->flashMessenger()->addErrorMessage('Serviço não encontrado ou indisponível.');
                return $this->redirect()->toRoute('appointments');
            }
        }

        $form = new AppointmentForm($this->serviceService);
        if ($service) {
            $form->setData(['service_id' => $service->getId()]);
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                try {
                    $data = $form->getData();
                    $this->appointmentService->createAppointment($data, $user);

                    $this->flashMessenger()->addSuccessMessage('Agendamento realizado com sucesso!');
                    return $this->redirect()->toRoute('appointments');
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage($e->getMessage());
                }
            }
        }

        return new ViewModel([
            'form' => $form,
            'service' => $service,
        ]);
    }

    public function viewAction()
    {
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return $this->redirect()->toRoute('login');
        }

        $id = $this->params()->fromRoute('id');
        $appointment = $this->appointmentService->getAppointment((int) $id);

        if (!$appointment || ($user->getRole() !== 'admin' && $appointment->getUser()->getId() !== $user->getId())) {
            $this->flashMessenger()->addErrorMessage('Agendamento não encontrado.');
            return $this->redirect()->toRoute('appointments');
        }

        return new ViewModel([
            'appointment' => $appointment,
        ]);
    }

    public function cancelAction()
    {
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return $this->redirect()->toRoute('login');
        }

        $id = $this->params()->fromRoute('id');
        
        try {
            $this->appointmentService->deleteAppointment((int) $id, $user);
            $this->flashMessenger()->addSuccessMessage('Agendamento cancelado com sucesso!');
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage($e->getMessage());
        }

        return $this->redirect()->toRoute('appointments');
    }

    public function getAvailableTimesAction()
    {
        $date = $this->params()->fromQuery('date');
        $serviceId = $this->params()->fromQuery('service_id');

        if (!$date || !$serviceId) {
            return new JsonModel([
                'success' => false,
                'message' => 'Parâmetros inválidos',
            ]);
        }

        try {
            $service = $this->serviceService->getService((int) $serviceId);
            if (!$service) {
                throw new \Exception('Serviço não encontrado');
            }

            $availableTimes = $this->appointmentService->getAvailableTimes(
                new \DateTime($date),
                $service
            );

            return new JsonModel([
                'success' => true,
                'data' => $availableTimes,
            ]);
        } catch (\Exception $e) {
            return new JsonModel([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
} 