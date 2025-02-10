<?php

declare(strict_types=1);

namespace Application\Repository;

use Application\Entity\Appointment;
use Application\Entity\Service;
use Application\Entity\User;
use DateTime;
use Doctrine\ORM\EntityRepository;

class AppointmentRepository extends EntityRepository
{
    public function findByUser(User $user): array
    {
        return $this->findBy(
            ['user' => $user],
            ['appointmentDate' => 'ASC']
        );
    }

    public function findUpcomingByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('a');
        
        return $qb->where('a.user = :user')
            ->andWhere('a.status IN (:statuses)')
            ->andWhere('a.appointmentDate >= :now')
            ->setParameter('user', $user)
            ->setParameter('statuses', [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_CONFIRMED
            ])
            ->setParameter('now', new DateTime())
            ->orderBy('a.appointmentDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findUpcomingAppointments(): array
    {
        $qb = $this->createQueryBuilder('a');
        $qb->where('a.appointmentDate >= :now')
           ->andWhere('a.status IN (:statuses)')
           ->setParameter('now', new DateTime())
           ->setParameter('statuses', [
               Appointment::STATUS_PENDING,
               Appointment::STATUS_CONFIRMED
           ])
           ->orderBy('a.appointmentDate', 'ASC')
           ->setMaxResults(10);

        return $qb->getQuery()->getResult();
    }

    public function findConflictingAppointments(
        Service $service,
        DateTime $appointmentDate
    ): array {
        $qb = $this->createQueryBuilder('a');
        
        $startTime = clone $appointmentDate;
        $endTime = clone $appointmentDate;
        $endTime->modify('+' . $service->getDuration() . ' minutes');

        return $qb->where('a.service = :service')
            ->andWhere('a.appointmentDate < :endTime')
            ->andWhere('DATE_ADD(a.appointmentDate, a.service.duration, \'MINUTE\') > :startTime')
            ->andWhere('a.status IN (:statuses)')
            ->setParameter('service', $service)
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime)
            ->setParameter('statuses', [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_CONFIRMED
            ])
            ->getQuery()
            ->getResult();
    }

    public function getAvailableTimes(Service $service, DateTime $date): array
    {
        // Horário de funcionamento do cartório (8h às 17h)
        $startHour = 8;
        $endHour = 17;
        
        // Duração do serviço em minutos
        $duration = $service->getDuration();
        
        // Gera todos os horários possíveis em intervalos de 30 minutos
        $times = [];
        $currentTime = new DateTime($date->format('Y-m-d') . ' ' . $startHour . ':00:00');
        $endTime = new DateTime($date->format('Y-m-d') . ' ' . $endHour . ':00:00');
        
        while ($currentTime <= $endTime) {
            // Verifica se há tempo suficiente para realizar o serviço antes do fechamento
            $serviceEndTime = clone $currentTime;
            $serviceEndTime->modify('+' . $duration . ' minutes');
            
            if ($serviceEndTime <= $endTime) {
                // Verifica se há conflito com outros agendamentos
                $conflicts = $this->findConflictingAppointments(
                    $service,
                    $currentTime
                );
                
                if (empty($conflicts)) {
                    $times[] = $currentTime->format('H:i');
                }
            }
            
            $currentTime->modify('+30 minutes');
        }
        
        return $times;
    }
} 