<?php

declare(strict_types=1);

namespace Application\Service;

use Application\Entity\User;
use Doctrine\ORM\EntityManager;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Result;

class UserService
{
    private EntityManager $entityManager;
    private AuthenticationService $authService;

    public function __construct(
        EntityManager $entityManager,
        AuthenticationService $authService
    ) {
        $this->entityManager = $entityManager;
        $this->authService = $authService;
    }

    public function createUser(array $data): User
    {
        $user = new User();
        $user->setName($data['name'])
            ->setEmail($data['email'])
            ->setPassword($data['password'])
            ->setPhone($data['phone'] ?? null);

        if (isset($data['role'])) {
            $user->setRole($data['role']);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function updateUser(int $id, array $data): ?User
    {
        $user = $this->entityManager->find(User::class, $id);
        if (!$user) {
            return null;
        }

        if (isset($data['name'])) {
            $user->setName($data['name']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['password'])) {
            $user->setPassword($data['password']);
        }
        if (isset($data['phone'])) {
            $user->setPhone($data['phone']);
        }
        if (isset($data['role'])) {
            $user->setRole($data['role']);
        }

        $this->entityManager->flush();

        return $user;
    }

    public function getUser(int $id): ?User
    {
        return $this->entityManager->find(User::class, $id);
    }

    public function deleteUser(int $id): bool
    {
        $user = $this->entityManager->find(User::class, $id);
        if (!$user) {
            return false;
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return true;
    }

    public function authenticate(string $email, string $password): Result
    {
        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if (!$user) {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null);
        }

        if (!$user->verifyPassword($password)) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, null);
        }

        return new Result(Result::SUCCESS, $user);
    }

    public function getCurrentUser(): ?User
    {
        if (!$this->authService->hasIdentity()) {
            return null;
        }

        return $this->authService->getIdentity();
    }

    public function listUsers(array $criteria = []): array
    {
        $repository = $this->entityManager->getRepository(User::class);
        return $repository->findBy($criteria);
    }
} 