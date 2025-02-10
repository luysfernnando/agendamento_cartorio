<?php

declare(strict_types=1);

namespace Application\Factory\Controller;

use Application\Controller\AdminController;
use Application\Service\AppointmentService;
use Application\Service\ServiceService;
use Application\Service\UserService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AdminControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AdminController
    {
        $appointmentService = $container->get(AppointmentService::class);
        $serviceService = $container->get(ServiceService::class);
        $userService = $container->get(UserService::class);

        return new AdminController($appointmentService, $serviceService, $userService);
    }
} 