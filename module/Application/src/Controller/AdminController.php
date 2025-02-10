<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Service\AppointmentService;
use Application\Service\ServiceService;
use Application\Service\UserService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

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
        if (!$this->isAdmin()) {
            return $this->redirect()->toRoute('home');
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
        if (!$this->isAdmin()) {
            return $this->redirect()->toRoute('home');
        }

        $users = $this->userService->listUsers();

        return new ViewModel([
            'users' => $users
        ]);
    }

    public function servicesAction()
    {
        if (!$this->isAdmin()) {
            return $this->redirect()->toRoute('home');
        }

        $services = $this->serviceService->listServices();

        return new ViewModel([
            'services' => $services
        ]);
    }

    public function appointmentsAction()
    {
        if (!$this->isAdmin()) {
            return $this->redirect()->toRoute('home');
        }

        $appointments = $this->appointmentService->listAppointments();

        return new ViewModel([
            'appointments' => $appointments
        ]);
    }

    private function isAdmin(): bool
    {
        $user = $this->userService->getCurrentUser();
        return $user && $user->getRole() === 'admin';
    }
} 