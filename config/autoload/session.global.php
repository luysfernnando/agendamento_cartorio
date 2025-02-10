<?php

declare(strict_types=1);

use Laminas\Session\Storage\SessionArrayStorage;

return [
    'session_config' => [
        'cookie_lifetime' => 60 * 60 * 1, // 1 hora
        'gc_maxlifetime' => 60 * 60 * 24 * 30, // 30 dias
        'cookie_httponly' => true,
        'cookie_secure' => false, // Mude para true em produção com HTTPS
        'use_cookies' => true,
        'remember_me_seconds' => 60 * 60 * 24 * 30, // 30 dias
    ],
    'session_storage' => [
        'type' => SessionArrayStorage::class,
    ],
    'session_containers' => [
        'Laminas\Session\Container',
    ],
]; 