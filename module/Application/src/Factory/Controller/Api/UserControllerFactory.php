<?php

declare(strict_types=1);

namespace Application\Factory\Controller\Api;

use Application\Controller\Api\UserController;
use Application\Service\UserService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class UserControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UserController
    {
        $userService = $container->get(UserService::class);
        return new UserController($userService);
    }
} 