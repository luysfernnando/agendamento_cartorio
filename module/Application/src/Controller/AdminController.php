<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Service\AppointmentService;
use Application\Service\ServiceService;
use Application\Service\UserService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Application\Entity\User;

class AdminController extends AbstractActionController
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
        if ($response = $this->requireAuth()) {
            return $response;
        }
        if ($response = $this->requireAdmin()) {
            return $response;
        }

        $totalUsers = count($this->userService->listUsers());
        $totalServices = count($this->serviceService->listServices(['active' => true]));
        $totalAppointments = count($this->appointmentService->listAppointments());
        $upcomingAppointments = $this->appointmentService->findUpcomingAppointments();

        return new ViewModel([
            'totalUsers' => $totalUsers,
            'totalServices' => $totalServices,
            'totalAppointments' => $totalAppointments,
            'upcomingAppointments' => $upcomingAppointments
        ]);
    }

    public function usersAction()
    {
        if ($response = $this->requireAuth()) {
            return $response;
        }
        if ($response = $this->requireAdmin()) {
            return $response;
        }

        $users = $this->userService->listUsers();

        return new ViewModel([
            'users' => $users
        ]);
    }

    public function servicesAction()
    {
        if ($response = $this->requireAuth()) {
            return $response;
        }
        if ($response = $this->requireAdmin()) {
            return $response;
        }

        $services = $this->serviceService->listServices();

        return new ViewModel([
            'services' => $services
        ]);
    }

    public function appointmentsAction()
    {
        if ($response = $this->requireAuth()) {
            return $response;
        }
        if ($response = $this->requireAdmin()) {
            return $response;
        }

        $appointments = $this->appointmentService->listAppointments();

        return new ViewModel([
            'appointments' => $appointments
        ]);
    }

    public function addServiceAction()
    {
        if ($response = $this->requireAuth()) {
            return $response;
        }
        if ($response = $this->requireAdmin()) {
            return $response;
        }

        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            
            try {
                $this->serviceService->createService($data);
                $this->flashMessenger()->addSuccessMessage('Serviço criado com sucesso!');
                return $this->redirect()->toRoute('admin', ['action' => 'services']);
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage('Erro ao criar serviço: ' . $e->getMessage());
            }
        }

        return new ViewModel();
    }

    private function requireAuth()
    {
        if (!$this->identity()) {
            $this->flashMessenger()->addErrorMessage('Você precisa estar autenticado para acessar esta área.');
            return $this->redirect()->toRoute('login');
        }
        return null;
    }

    private function requireAdmin()
    {
        $user = $this->identity();
        if (!$user || $user->getRole() !== 'admin') {
            $this->flashMessenger()->addErrorMessage('Você não tem permissão para acessar esta área.');
            return $this->redirect()->toRoute('home');
        }
        return null;
    }
} 