<?php

declare(strict_types=1);

namespace Application\Controller\Api;

use Application\Service\UserService;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractRestfulController;
use Laminas\View\Model\JsonModel;

class UserController extends AbstractRestfulController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function create($data)
    {
        try {
            $user = $this->userService->createUser($data);
            return new JsonModel([
                'success' => true,
                'data' => $user->toArray(),
            ]);
        } catch (\Exception $e) {
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_400);
            return new JsonModel([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function get($id)
    {
        $user = $this->userService->getUser((int) $id);
        if (!$user) {
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_404);
            return new JsonModel([
                'success' => false,
                'message' => 'Usuário não encontrado',
            ]);
        }

        return new JsonModel([
            'success' => true,
            'data' => $user->toArray(),
        ]);
    }

    public function update($id, $data)
    {
        try {
            $user = $this->userService->updateUser((int) $id, $data);
            if (!$user) {
                $this->getResponse()->setStatusCode(Response::STATUS_CODE_404);
                return new JsonModel([
                    'success' => false,
                    'message' => 'Usuário não encontrado',
                ]);
            }

            return new JsonModel([
                'success' => true,
                'data' => $user->toArray(),
            ]);
        } catch (\Exception $e) {
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_400);
            return new JsonModel([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function delete($id)
    {
        $result = $this->userService->deleteUser((int) $id);
        if (!$result) {
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_404);
            return new JsonModel([
                'success' => false,
                'message' => 'Usuário não encontrado',
            ]);
        }

        return new JsonModel([
            'success' => true,
            'message' => 'Usuário excluído com sucesso',
        ]);
    }

    public function getList()
    {
        $users = $this->userService->listUsers();
        return new JsonModel([
            'success' => true,
            'data' => array_map(fn($user) => $user->toArray(), $users),
        ]);
    }

    public function loginAction()
    {
        $data = $this->getRequest()->getPost()->toArray();
        
        if (!isset($data['email']) || !isset($data['password'])) {
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_400);
            return new JsonModel([
                'success' => false,
                'message' => 'Email e senha são obrigatórios',
            ]);
        }

        $result = $this->userService->authenticate($data['email'], $data['password']);
        
        if (!$result->isValid()) {
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_401);
            return new JsonModel([
                'success' => false,
                'message' => 'Credenciais inválidas',
            ]);
        }

        return new JsonModel([
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'data' => $result->getIdentity()->toArray(),
        ]);
    }

    public function logoutAction()
    {
        $this->userService->getCurrentUser();
        return new JsonModel([
            'success' => true,
            'message' => 'Logout realizado com sucesso',
        ]);
    }
} 