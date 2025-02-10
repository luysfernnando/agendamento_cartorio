<?php

declare(strict_types=1);

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => \Doctrine\DBAL\Driver\PDO\MySQL\Driver::class,
                'params' => [
                    'host'     => 'mysql',
                    'port'     => '3306',
                    'user'     => 'root',
                    'password' => 'root',
                    'dbname'   => 'cartorio',
                    'charset'  => 'utf8mb4',
                ],
            ],
        ],
        'configuration' => [
            'orm_default' => [
                'metadata_cache'    => 'array',
                'query_cache'       => 'array',
                'result_cache'      => 'array',
                'hydration_cache'   => 'array',
                'driver'           => 'orm_default',
                'generate_proxies' => true,
                'proxy_dir'        => 'data/DoctrineORMModule/Proxy',
                'proxy_namespace'  => 'DoctrineORMModule\Proxy',
                'filters'          => [],
                'datetime_functions' => [],
                'string_functions' => [],
                'numeric_functions' => [],
            ],
        ],
        'driver' => [
            'application_entities' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [__DIR__ . '/../../module/Application/src/Entity'],
            ],
            'orm_default' => [
                'drivers' => [
                    'Application\Entity' => 'application_entities',
                ],
            ],
        ],
        'entitymanager' => [
            'orm_default' => [
                'connection'    => 'orm_default',
                'configuration' => 'orm_default',
            ],
        ],
        'authentication' => [
            'orm_default' => [
                'object_manager' => 'Doctrine\ORM\EntityManager',
                'identity_class' => 'Application\Entity\User',
                'identity_property' => 'email',
                'credential_property' => 'password',
                'credential_callable' => function(\Application\Entity\User $user, $passwordGiven) {
                    return $user->verifyPassword($passwordGiven);
                },
            ],
        ],
        'migrations_configuration' => [
            'orm_default' => [
                'table_storage' => [
                    'table_name' => 'migrations',
                    'version_column_name' => 'version',
                    'version_column_length' => 191,
                    'executed_at_column_name' => 'executed_at',
                    'execution_time_column_name' => 'execution_time',
                ],
                'migrations_paths' => [
                    'Application\Migrations' => 'module/Application/src/Migrations'
                ],
                'all_or_nothing' => true,
                'check_database_platform' => true,
            ],
        ],
    ],
]; 