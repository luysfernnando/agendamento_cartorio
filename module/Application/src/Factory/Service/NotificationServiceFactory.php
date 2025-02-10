<?php

declare(strict_types=1);

namespace Application\Factory\Service;

use Application\Service\NotificationService;
use Doctrine\ORM\EntityManager;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class NotificationServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): NotificationService
    {
        $config = $container->get('config');
        $notificationConfig = $config['notification'] ?? [];

        $entityManager = $container->get(EntityManager::class);
        $mailTransport = $container->get(TransportInterface::class);

        return new NotificationService(
            $entityManager,
            $mailTransport,
            $notificationConfig['email_from'] ?? 'no-reply@cartorio.com.br',
            $notificationConfig['sms_api_key'] ?? '',
            $notificationConfig['sms_api_url'] ?? ''
        );
    }
} 