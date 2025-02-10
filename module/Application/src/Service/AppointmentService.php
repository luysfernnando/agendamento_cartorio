<?php

declare(strict_types=1);

namespace Application\Service;

use Application\Entity\Appointment;
use Application\Entity\Service;
use Application\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManager;
use InvalidArgumentException;

class AppointmentService
{
    private EntityManager $entityManager;
    private NotificationService $notificationService;

    public function __construct(
        EntityManager $entityManager,
        NotificationService $notificationService
    ) {
        $this->entityManager = $entityManager;
        $this->notificationService = $notificationService;
    }

    public function createAppointment(array $data, User $user): Appointment
    {
        $service = $this->entityManager->find(Service::class, $data['service_id']);
        if (!$service) {
            throw new InvalidArgumentException('Serviço não encontrado');
        }

        if (!$service->isActive()) {
            throw new InvalidArgumentException('Este serviço não está disponível no momento');
        }

        $appointmentDate = new DateTime($data['appointment_date']);
        if ($appointmentDate < new DateTime()) {
            throw new InvalidArgumentException('A data do agendamento não pode ser no passado');
        }

        // Verifica se já existe agendamento no mesmo horário
        if ($this->hasConflictingAppointment($appointmentDate, $service->getDuration())) {
            throw new InvalidArgumentException('Já existe um agendamento neste horário');
        }

        $appointment = new Appointment();
        $appointment->setUser($user)
            ->setService($service)
            ->setAppointmentDate($appointmentDate)
            ->setNotes($data['notes'] ?? null);

        $this->entityManager->persist($appointment);
        $this->entityManager->flush();

        // Envia notificação de criação do agendamento
        $this->notificationService->sendAppointmentNotification(
            $appointment,
            'Seu agendamento foi criado com sucesso!'
        );

        return $appointment;
    }

    public function updateAppointment(int $id, array $data, User $user): ?Appointment
    {
        $appointment = $this->entityManager->find(Appointment::class, $id);
        if (!$appointment) {
            return null;
        }

        // Apenas o próprio usuário ou admin pode atualizar
        if (!$this->canManageAppointment($appointment, $user)) {
            throw new InvalidArgumentException('Você não tem permissão para atualizar este agendamento');
        }

        if (isset($data['service_id'])) {
            $service = $this->entityManager->find(Service::class, $data['service_id']);
            if (!$service) {
                throw new InvalidArgumentException('Serviço não encontrado');
            }
            if (!$service->isActive()) {
                throw new InvalidArgumentException('Este serviço não está disponível no momento');
            }
            $appointment->setService($service);
        }

        if (isset($data['appointment_date'])) {
            $appointmentDate = new DateTime($data['appointment_date']);
            if ($appointmentDate < new DateTime()) {
                throw new InvalidArgumentException('A data do agendamento não pode ser no passado');
            }
            if ($this->hasConflictingAppointment($appointmentDate, $appointment->getService()->getDuration(), $id)) {
                throw new InvalidArgumentException('Já existe um agendamento neste horário');
            }
            $appointment->setAppointmentDate($appointmentDate);
        }

        if (isset($data['notes'])) {
            $appointment->setNotes($data['notes']);
        }

        if (isset($data['status'])) {
            $appointment->setStatus($data['status']);
            
            // Envia notificação de alteração de status
            $this->notificationService->sendAppointmentNotification(
                $appointment,
                "Seu agendamento foi {$data['status']}"
            );
        }

        $this->entityManager->flush();

        return $appointment;
    }

    public function getAppointment(int $id): ?Appointment
    {
        return $this->entityManager->find(Appointment::class, $id);
    }

    public function deleteAppointment(int $id, User $user): bool
    {
        $appointment = $this->entityManager->find(Appointment::class, $id);
        if (!$appointment) {
            return false;
        }

        // Apenas o próprio usuário ou admin pode deletar
        if (!$this->canManageAppointment($appointment, $user)) {
            throw new InvalidArgumentException('Você não tem permissão para cancelar este agendamento');
        }

        // Não permite cancelar agendamentos já realizados
        if ($appointment->getStatus() === Appointment::STATUS_COMPLETED) {
            throw new InvalidArgumentException('Não é possível cancelar um agendamento já realizado');
        }

        $appointment->setStatus(Appointment::STATUS_CANCELLED);
        $this->entityManager->flush();

        // Envia notificação de cancelamento
        $this->notificationService->sendAppointmentNotification(
            $appointment,
            'Seu agendamento foi cancelado'
        );

        return true;
    }

    public function listAppointments(array $criteria = [], ?User $user = null): array
    {
        if ($user && $user->getRole() !== 'admin') {
            $criteria['user'] = $user;
        }

        $repository = $this->entityManager->getRepository(Appointment::class);
        return $repository->findBy($criteria, ['appointmentDate' => 'ASC']);
    }

    public function getUpcomingAppointments(?User $user = null): array
    {
        $criteria = [
            'status' => [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_CONFIRMED
            ],
        ];

        if ($user && $user->getRole() !== 'admin') {
            $criteria['user'] = $user;
        }

        $repository = $this->entityManager->getRepository(Appointment::class);
        return $repository->findBy(
            $criteria,
            ['appointmentDate' => 'ASC']
        );
    }

    private function hasConflictingAppointment(DateTime $date, int $duration, ?int $excludeId = null): bool
    {
        $endDate = (clone $date)->modify("+{$duration} minutes");
        
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a')
           ->from(Appointment::class, 'a')
           ->where('a.status != :cancelled')
           ->andWhere('a.appointmentDate < :endDate')
           ->andWhere('DATE_ADD(a.appointmentDate, a.service.duration, \'minute\') > :startDate')
           ->setParameter('cancelled', Appointment::STATUS_CANCELLED)
           ->setParameter('startDate', $date)
           ->setParameter('endDate', $endDate);

        if ($excludeId) {
            $qb->andWhere('a.id != :id')
               ->setParameter('id', $excludeId);
        }

        return count($qb->getQuery()->getResult()) > 0;
    }

    private function canManageAppointment(Appointment $appointment, User $user): bool
    {
        return $user->getRole() === 'admin' || $appointment->getUser()->getId() === $user->getId();
    }
} 