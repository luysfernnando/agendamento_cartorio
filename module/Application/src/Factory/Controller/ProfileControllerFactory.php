<?php

declare(strict_types=1);

namespace Application\Factory\Controller;

use Application\Controller\ProfileController;
use Application\Service\UserService;
use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ProfileControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ProfileController
    {
        $userService = $container->get(UserService::class);
        $authService = $container->get(AuthenticationService::class);
        
        return new ProfileController($userService, $authService);
    }
} 