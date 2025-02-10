<?php

declare(strict_types=1);

namespace Application;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\ViewHelperProviderInterface;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\EventManager\EventInterface;
use Laminas\Session\SessionManager;
use Laminas\Session\Container;

class Module implements 
    ConfigProviderInterface, 
    ViewHelperProviderInterface,
    BootstrapListenerInterface
{
    private static bool $sessionInitialized = false;

    public function getConfig(): array
    {
        /** @var array $config */
        $config = include __DIR__ . '/../config/module.config.php';
        return $config;
    }

    public function getViewHelperConfig(): array
    {
        return [
            'factories' => [
                View\Helper\FormatCurrency::class => \Laminas\ServiceManager\Factory\InvokableFactory::class,
                View\Helper\IsAdmin::class => View\Helper\Factory\IsAdminFactory::class,
            ],
            'aliases' => [
                'formatCurrency' => View\Helper\FormatCurrency::class,
                'isAdmin' => View\Helper\IsAdmin::class,
            ],
        ];
    }

    public function onBootstrap(EventInterface $e)
    {
        if (!self::$sessionInitialized) {
            $application = $e->getApplication();
            $container = $application->getServiceManager();
            
            // Inicializa a sessão
            $sessionManager = $container->get(SessionManager::class);
            
            // Define o gerenciador de sessão padrão para os containers
            Container::setDefaultManager($sessionManager);
            
            self::$sessionInitialized = true;
        }
    }
}
