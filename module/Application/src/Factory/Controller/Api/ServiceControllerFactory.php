<?php

declare(strict_types=1);

namespace Application\Factory\Controller\Api;

use Application\Controller\Api\ServiceController;
use Application\Service\ServiceService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ServiceControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ServiceController
    {
        $serviceService = $container->get(ServiceService::class);
        return new ServiceController($serviceService);
    }
} 