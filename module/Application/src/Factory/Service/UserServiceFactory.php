<?php

declare(strict_types=1);

namespace Application\Factory\Service;

use Application\Service\UserService;
use Doctrine\ORM\EntityManager;
use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class UserServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UserService
    {
        $entityManager = $container->get(EntityManager::class);
        $authService = $container->get(AuthenticationService::class);

        return new UserService($entityManager, $authService);
    }
} 