<?php

declare(strict_types=1);

namespace Application\Factory\Service;

use Application\Service\ServiceService;
use Doctrine\ORM\EntityManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ServiceServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ServiceService
    {
        $entityManager = $container->get(EntityManager::class);
        return new ServiceService($entityManager);
    }
} 