<?php

declare(strict_types=1);

namespace Application\Command\Factory;

use Application\Command\CreateAdminUserCommand;
use Application\Service\UserService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CreateAdminUserCommandFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CreateAdminUserCommand
    {
        $userService = $container->get(UserService::class);
        return new CreateAdminUserCommand($userService);
    }
} 