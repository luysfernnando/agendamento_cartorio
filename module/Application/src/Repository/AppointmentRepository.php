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
            ['appointmentDate' => 'ASC', 'appointmentTime' => 'ASC']
        );
    }

    public function findUpcomingByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('a');
        
        return $qb->where('a.user = :user')
            ->andWhere('a.status != :status')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->gt('a.appointmentDate', ':today'),
                    $qb->expr()->andX(
                        $qb->expr()->eq('a.appointmentDate', ':today'),
                        $qb->expr()->gte('a.appointmentTime', ':now')
                    )
                )
            )
            ->setParameter('user', $user)
            ->setParameter('status', 'cancelled')
            ->setParameter('today', new DateTime('today'))
            ->setParameter('now', new DateTime('now'))
            ->orderBy('a.appointmentDate', 'ASC')
            ->addOrderBy('a.appointmentTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findConflictingAppointments(
        Service $service,
        DateTime $date,
        DateTime $time
    ): array {
        $qb = $this->createQueryBuilder('a');
        
        $startTime = clone $time;
        $endTime = clone $time;
        $endTime->modify('+' . $service->getDuration() . ' minutes');

        return $qb->where('a.appointmentDate = :date')
            ->andWhere('a.status != :status')
            ->andWhere(
                $qb->expr()->orX(
                    // Novo agendamento começa durante outro agendamento
                    $qb->expr()->andX(
                        $qb->expr()->gte('a.appointmentTime', ':startTime'),
                        $qb->expr()->lt('a.appointmentTime', ':endTime')
                    ),
                    // Novo agendamento termina durante outro agendamento
                    $qb->expr()->andX(
                        $qb->expr()->lt('a.appointmentTime', ':startTime'),
                        $qb->expr()->gt(
                            'DATE_ADD(a.appointmentTime, a.service.duration, \'minute\')',
                            ':startTime'
                        )
                    )
                )
            )
            ->setParameter('date', $date)
            ->setParameter('status', 'cancelled')
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime)
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
                    $date,
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