<?php

declare(strict_types=1);

namespace Application\Factory\Service;

use Application\Service\UserService;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Storage\Session as SessionStorage;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AuthenticationServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AuthenticationService
    {
        $userService = $container->get(UserService::class);
        $authService = new AuthenticationService(
            new SessionStorage('Auth')
        );
        $authService->setAdapter($userService);
        return $authService;
    }
} 