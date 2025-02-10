<?php

declare(strict_types=1);

namespace Application\Factory\Service;

use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Storage\Session as SessionStorage;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AuthenticationServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AuthenticationService
    {
        $sessionStorage = new SessionStorage('Auth');
        return new AuthenticationService($sessionStorage);
    }
} 