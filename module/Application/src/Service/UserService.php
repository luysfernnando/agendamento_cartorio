<?php

declare(strict_types=1);

namespace Application\Service;

use Application\Entity\User;
use Doctrine\ORM\EntityManager;
use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Result;
use Laminas\Crypt\Password\Bcrypt;
use Laminas\Authentication\AuthenticationService;

class UserService implements AdapterInterface
{
    private EntityManager $entityManager;
    private ?string $email = null;
    private ?string $password = null;
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
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setPhone($data['phone'] ?? null);
        
        // Criptografa a senha
        $bcrypt = new Bcrypt();
        $user->setPassword($bcrypt->create($data['password']));
        
        // Define o papel (se não especificado, usa o padrão 'client')
        $user->setRole($data['role'] ?? User::ROLE_CLIENT);

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
            $bcrypt = new Bcrypt();
            $user->setPassword($bcrypt->create($data['password']));
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

    public function getUserByEmail(string $email): ?User
    {
        return $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $email]);
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

    public function setIdentity($email)
    {
        $this->email = $email;
        return $this;
    }

    public function setCredential($password)
    {
        $this->password = $password;
        return $this;
    }

    public function authenticate()
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $this->email]);

        if (!$user) {
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND,
                null,
                ['Usuário não encontrado']
            );
        }

        $bcrypt = new Bcrypt();
        if (!$bcrypt->verify($this->password, $user->getPassword())) {
            return new Result(
                Result::FAILURE_CREDENTIAL_INVALID,
                null,
                ['Senha incorreta']
            );
        }

        return new Result(
            Result::SUCCESS,
            $user,
            ['Autenticação realizada com sucesso']
        );
    }

    public function listUsers(array $criteria = []): array
    {
        $repository = $this->entityManager->getRepository(User::class);
        return $repository->findBy($criteria);
    }

    public function getCurrentUser(): ?User
    {
        if (!$this->authService->hasIdentity()) {
            return null;
        }

        return $this->authService->getIdentity();
    }
} 