<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Form\LoginForm;
use Application\Form\RegisterForm;
use Application\Service\UserService;
use Laminas\Authentication\AuthenticationService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class AuthController extends AbstractActionController
{
    private UserService $userService;
    private AuthenticationService $authService;

    public function __construct(
        UserService $userService,
        AuthenticationService $authService
    ) {
        $this->userService = $userService;
        $this->authService = $authService;
    }

    public function loginAction()
    {
        if ($this->authService->hasIdentity()) {
            return $this->redirect()->toRoute('home');
        }

        $form = new LoginForm();
        $error = null;

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();

                // Configura o adaptador de autenticação
                $this->userService->setIdentity($data['email']);
                $this->userService->setCredential($data['password']);
                $this->authService->setAdapter($this->userService);

                // Tenta autenticar
                $result = $this->authService->authenticate();

                if ($result->isValid()) {
                    $this->flashMessenger()->addSuccessMessage('Login realizado com sucesso!');
                    return $this->redirect()->toRoute('home');
                }

                $messages = $result->getMessages();
                $error = !empty($messages) ? $messages[0] : 'Credenciais inválidas';
            }
        }

        return new ViewModel([
            'form' => $form,
            'error' => $error,
        ]);
    }

    public function registerAction()
    {
        if ($this->authService->hasIdentity()) {
            return $this->redirect()->toRoute('home');
        }

        $form = new RegisterForm();
        $error = null;

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                try {
                    $data = $form->getData();

                    // Verifica se o e-mail já está em uso
                    if ($this->userService->getUserByEmail($data['email'])) {
                        $error = 'Este e-mail já está em uso';
                    } else {
                        $this->userService->createUser($data);

                        $this->flashMessenger()->addSuccessMessage(
                            'Cadastro realizado com sucesso! Faça login para continuar.'
                        );
                        return $this->redirect()->toRoute('login');
                    }
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        return new ViewModel([
            'form' => $form,
            'error' => $error,
        ]);
    }

    public function logoutAction()
    {
        $this->authService->clearIdentity();
        $this->flashMessenger()->addSuccessMessage('Você foi desconectado com sucesso!');
        return $this->redirect()->toRoute('home');
    }
} 