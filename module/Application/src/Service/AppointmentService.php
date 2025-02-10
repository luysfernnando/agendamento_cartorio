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
    private AppointmentRepository $repository;

    public function __construct(
        EntityManager $entityManager,
        AuthenticationService $authService,
        NotificationService $notificationService
    ) {
        $this->entityManager = $entityManager;
        $this->authService = $authService;
        $this->notificationService = $notificationService;
        $this->repository = $entityManager->getRepository(Appointment::class);
    }

    public function getRepository(): AppointmentRepository
    {
        return $this->repository;
    }

    public function findById(int $id): ?Appointment
    {
        return $this->repository->find($id);
    }

    public function findByUser(User $user): array
    {
        return $this->repository->findByUser($user);
    }

    public function listAppointments(array $criteria = [], ?User $user = null): array
    {
        if ($user) {
            return $this->findByUser($user);
        }

        return $this->repository->findBy(
            $criteria,
            ['appointmentDate' => 'ASC']
        );
    }

    public function findUpcomingByUser(User $user): array
    {
        return $this->repository->findUpcomingByUser($user);
    }

    public function getAvailableTimes(Service $service, DateTime $date): array
    {
        return $this->repository->getAvailableTimes($service, $date);
    }

    public function schedule(
        User $user,
        Service $service,
        DateTime $appointmentDate,
        ?string $notes = null
    ): Appointment {
        // Verifica se há conflitos
        $conflicts = $this->repository->findConflictingAppointments(
            $service,
            $appointmentDate
        );

        if (!empty($conflicts)) {
            throw new Exception('O horário selecionado não está disponível.');
        }

        $appointment = new Appointment();
        $appointment->setUser($user);
        $appointment->setService($service);
        $appointment->setAppointmentDate($appointmentDate);
        $appointment->setNotes($notes);

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

    public function findUpcomingAppointments(): array
    {
        return $this->repository->findUpcomingAppointments();
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