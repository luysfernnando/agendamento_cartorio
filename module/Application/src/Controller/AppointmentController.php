<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Entity\Appointment;
use Application\Form\AppointmentForm;
use Application\Service\AppointmentService;
use Application\Service\ServiceService;
use Application\Service\UserService;
use DateTime;
use Exception;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class AppointmentController extends AbstractController
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

    public function indexAction(): ViewModel
    {
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return $this->redirect()->toRoute('login');
        }

        $appointments = $this->appointmentService->findByUser($user);

        return new ViewModel([
            'appointments' => $appointments
        ]);
    }

    public function scheduleAction(): ViewModel
    {
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return $this->redirect()->toRoute('login');
        }

        $form = new AppointmentForm($this->serviceService);
        $services = $this->serviceService->listActiveServices();

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());

            if ($form->isValid()) {
                try {
                    $data = $form->getData();
                    
                    $service = $this->serviceService->findById((int) $data['service_id']);
                    if (!$service) {
                        throw new Exception('Serviço não encontrado');
                    }

                    $appointmentDate = new DateTime($data['appointment_date'] . ' ' . $data['appointment_time']);
                    
                    $this->appointmentService->schedule(
                        $user,
                        $service,
                        $appointmentDate,
                        $data['notes'] ?? null
                    );

                    $this->flashMessenger()->addSuccessMessage('Agendamento realizado com sucesso!');
                    return $this->redirect()->toRoute('appointment');
                } catch (Exception $e) {
                    $this->flashMessenger()->addErrorMessage($e->getMessage());
                }
            }
        }

        return new ViewModel([
            'form' => $form,
            'services' => $services
        ]);
    }

    public function viewAction(): ViewModel
    {
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return $this->redirect()->toRoute('login');
        }

        $id = (int) $this->params()->fromRoute('id');
        $appointment = $this->appointmentService->findById($id);

        if (!$appointment || $appointment->getUser()->getId() !== $user->getId()) {
            $this->flashMessenger()->addErrorMessage('Agendamento não encontrado');
            return $this->redirect()->toRoute('appointment');
        }

        return new ViewModel([
            'appointment' => $appointment
        ]);
    }

    public function cancelAction(): Response
    {
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return $this->redirect()->toRoute('login');
        }

        $id = (int) $this->params()->fromRoute('id');
        $appointment = $this->appointmentService->findById($id);

        if (!$appointment || $appointment->getUser()->getId() !== $user->getId()) {
            $this->flashMessenger()->addErrorMessage('Agendamento não encontrado');
            return $this->redirect()->toRoute('appointment');
        }

        if (!$appointment->canBeCancelled()) {
            $this->flashMessenger()->addErrorMessage('Este agendamento não pode mais ser cancelado');
            return $this->redirect()->toRoute('appointment');
        }

        try {
            $this->appointmentService->cancel($appointment);
            $this->flashMessenger()->addSuccessMessage('Agendamento cancelado com sucesso!');
        } catch (Exception $e) {
            $this->flashMessenger()->addErrorMessage($e->getMessage());
        }

        return $this->redirect()->toRoute('appointment');
    }

    public function getAvailableTimesAction(): JsonModel
    {
        $serviceId = (int) $this->params()->fromQuery('service_id');
        $date = $this->params()->fromQuery('date');

        try {
            $service = $this->serviceService->findById($serviceId);
            if (!$service) {
                throw new Exception('Serviço não encontrado');
            }

            $dateTime = new DateTime($date);
            $times = $this->appointmentService->getAvailableTimes($service, $dateTime);

            return new JsonModel([
                'success' => true,
                'times' => $times
            ]);
        } catch (Exception $e) {
            return new JsonModel([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
} 