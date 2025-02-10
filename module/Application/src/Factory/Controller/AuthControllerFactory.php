<?php

declare(strict_types=1);

namespace Application\Factory\Controller;

use Application\Controller\AuthController;
use Application\Service\UserService;
use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AuthControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AuthController
    {
        $userService = $container->get(UserService::class);
        $authService = $container->get(AuthenticationService::class);

        return new AuthController($userService, $authService);
    }
} 