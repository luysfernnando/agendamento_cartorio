<?php

declare(strict_types=1);

use Application\Cache\DoctrineArrayCache;

return [
    'service_manager' => [
        'factories' => [
            DoctrineArrayCache::class => Application\Cache\Factory\DoctrineArrayCacheFactory::class,
        ],
        'aliases' => [
            'doctrine.cache.array' => DoctrineArrayCache::class,
        ],
    ],
    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'metadata_cache'    => 'array',
                'query_cache'       => 'array',
                'result_cache'      => 'array',
                'hydration_cache'   => 'array',
            ],
        ],
    ],
]; 