<?php

declare(strict_types=1);

namespace Application\View\Helper\Factory;

use Application\View\Helper\IsAdmin;
use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class IsAdminFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IsAdmin
    {
        $authService = $container->get(AuthenticationService::class);
        return new IsAdmin($authService);
    }
} 