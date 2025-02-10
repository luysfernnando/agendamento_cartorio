<?php

declare(strict_types=1);

namespace Application\Factory\Form;

use Application\Form\AppointmentForm;
use Application\Service\ServiceService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AppointmentFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AppointmentForm
    {
        $serviceService = $container->get(ServiceService::class);
        return new AppointmentForm($serviceService);
    }
} 