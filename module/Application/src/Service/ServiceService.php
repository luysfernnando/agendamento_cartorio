<?php

declare(strict_types=1);

namespace Application\Service;

use Application\Entity\Service;
use Doctrine\ORM\EntityManager;

class ServiceService
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findById(int $id): ?Service
    {
        return $this->entityManager->find(Service::class, $id);
    }

    public function createService(array $data): Service
    {
        $service = new Service();
        $service->setName($data['name'])
            ->setDescription($data['description'] ?? null)
            ->setDuration((int) $data['duration'])
            ->setPrice((float) $data['price']);

        if (isset($data['active'])) {
            $service->setActive((bool) $data['active']);
        }

        $this->entityManager->persist($service);
        $this->entityManager->flush();

        return $service;
    }

    public function updateService(int $id, array $data): ?Service
    {
        $service = $this->entityManager->find(Service::class, $id);
        if (!$service) {
            return null;
        }

        if (isset($data['name'])) {
            $service->setName($data['name']);
        }
        if (array_key_exists('description', $data)) {
            $service->setDescription($data['description']);
        }
        if (isset($data['duration'])) {
            $service->setDuration((int) $data['duration']);
        }
        if (isset($data['price'])) {
            $service->setPrice((float) $data['price']);
        }
        if (isset($data['active'])) {
            $service->setActive((bool) $data['active']);
        }

        $this->entityManager->flush();

        return $service;
    }

    public function getService(int $id): ?Service
    {
        return $this->entityManager->find(Service::class, $id);
    }

    public function deleteService(int $id): bool
    {
        $service = $this->entityManager->find(Service::class, $id);
        if (!$service) {
            return false;
        }

        $this->entityManager->remove($service);
        $this->entityManager->flush();

        return true;
    }

    public function listServices(array $criteria = []): array
    {
        $repository = $this->entityManager->getRepository(Service::class);
        
        // Se não especificado, lista apenas serviços ativos
        if (!isset($criteria['active'])) {
            $criteria['active'] = true;
        }

        return $repository->findBy($criteria);
    }

    public function toggleServiceStatus(int $id): ?Service
    {
        $service = $this->entityManager->find(Service::class, $id);
        if (!$service) {
            return null;
        }

        $service->setActive(!$service->isActive());
        $this->entityManager->flush();

        return $service;
    }

    public function getAvailableServices(): array
    {
        return $this->listServices(['active' => true]);
    }

    public function listActiveServices(): array
    {
        return $this->listServices(['active' => true]);
    }
} 