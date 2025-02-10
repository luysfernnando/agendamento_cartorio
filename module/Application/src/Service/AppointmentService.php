<?php

declare(strict_types=1);

namespace Application\Service;

use Application\Entity\Appointment;
use Application\Entity\Service;
use Application\Entity\User;
use Application\Repository\AppointmentRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Laminas\Authentication\AuthenticationService;

class AppointmentService
{
    private EntityManager $entityManager;
    private AuthenticationService $authService;
    private NotificationService $notificationService;

    public function __construct(
        EntityManager $entityManager,
        AuthenticationService $authService,
        NotificationService $notificationService
    ) {
        $this->entityManager = $entityManager;
        $this->authService = $authService;
        $this->notificationService = $notificationService;
    }

    public function getRepository(): AppointmentRepository
    {
        return $this->entityManager->getRepository(Appointment::class);
    }

    public function findById(int $id): ?Appointment
    {
        return $this->getRepository()->find($id);
    }

    public function findByUser(User $user): array
    {
        return $this->getRepository()->findByUser($user);
    }

    public function findUpcomingByUser(User $user): array
    {
        return $this->getRepository()->findUpcomingByUser($user);
    }

    public function getAvailableTimes(Service $service, DateTime $date): array
    {
        return $this->getRepository()->getAvailableTimes($service, $date);
    }

    public function schedule(array $data): Appointment
    {
        if (!$this->authService->hasIdentity()) {
            throw new Exception('Usuário não autenticado');
        }

        $user = $this->authService->getIdentity();
        $service = $this->entityManager->find(Service::class, $data['service_id']);

        if (!$service) {
            throw new Exception('Serviço não encontrado');
        }

        $appointmentDate = new DateTime($data['appointment_date']);
        $appointmentTime = DateTime::createFromFormat('H:i', $data['appointment_time']);

        // Verifica se o horário está disponível
        $conflicts = $this->getRepository()->findConflictingAppointments(
            $service,
            $appointmentDate,
            $appointmentTime
        );

        if (!empty($conflicts)) {
            throw new Exception('Horário não disponível');
        }

        $appointment = new Appointment();
        $appointment->setUser($user)
            ->setService($service)
            ->setAppointmentDate($appointmentDate)
            ->setAppointmentTime($appointmentTime)
            ->setNotes($data['notes'] ?? null);

        $this->entityManager->persist($appointment);
        $this->entityManager->flush();

        // Envia notificação de confirmação
        $this->notificationService->sendAppointmentConfirmation($appointment);

        return $appointment;
    }

    public function cancel(Appointment $appointment): void
    {
        if (!$appointment->canBeCancelled()) {
            throw new Exception('Este agendamento não pode ser cancelado');
        }

        $appointment->cancel();
        $this->entityManager->flush();

        // Envia notificação de cancelamento
        $this->notificationService->sendAppointmentCancellation($appointment);
    }

    public function validateUserAccess(Appointment $appointment): void
    {
        if (!$this->authService->hasIdentity()) {
            throw new Exception('Usuário não autenticado');
        }

        $user = $this->authService->getIdentity();
        if ($appointment->getUser()->getId() !== $user->getId()) {
            throw new Exception('Acesso negado');
        }
    }
} 