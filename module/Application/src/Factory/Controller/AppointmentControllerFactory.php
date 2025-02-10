<?php

declare(strict_types=1);

namespace Application\Factory\Controller;

use Application\Controller\AppointmentController;
use Application\Form\AppointmentForm;
use Application\Service\AppointmentService;
use Application\Service\ServiceService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AppointmentControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AppointmentController
    {
        $appointmentService = $container->get(AppointmentService::class);
        $serviceService = $container->get(ServiceService::class);
        $appointmentForm = $container->get(AppointmentForm::class);

        return new AppointmentController($appointmentService, $serviceService, $appointmentForm);
    }
} 