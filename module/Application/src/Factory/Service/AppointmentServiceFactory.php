<?php

declare(strict_types=1);

namespace Application\Factory\Service;

use Application\Service\AppointmentService;
use Application\Service\NotificationService;
use Doctrine\ORM\EntityManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AppointmentServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AppointmentService
    {
        $entityManager = $container->get(EntityManager::class);
        $notificationService = $container->get(NotificationService::class);

        return new AppointmentService($entityManager, $notificationService);
    }
} 