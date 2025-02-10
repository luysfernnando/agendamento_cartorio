<?php

declare(strict_types=1);

namespace Application\Cache\Factory;

use Application\Cache\DoctrineArrayCache;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DoctrineArrayCacheFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DoctrineArrayCache
    {
        return new DoctrineArrayCache();
    }
} 