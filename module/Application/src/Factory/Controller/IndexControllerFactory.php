<?php

declare(strict_types=1);

namespace Application\Factory\Controller;

use Application\Controller\IndexController;
use Application\Service\ServiceService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IndexController
    {
        $serviceService = $container->get(ServiceService::class);
        return new IndexController($serviceService);
    }
} 