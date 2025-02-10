<?php

declare(strict_types=1);

namespace Application\Factory\Controller\Api;

use Application\Controller\Api\AppointmentController;
use Application\Service\AppointmentService;
use Application\Service\UserService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AppointmentControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AppointmentController
    {
        $appointmentService = $container->get(AppointmentService::class);
        $userService = $container->get(UserService::class);

        return new AppointmentController($appointmentService, $userService);
    }
} 