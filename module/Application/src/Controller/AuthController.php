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
                
                $result = $this->userService->authenticate(
                    $data['email'],
                    $data['password']
                );

                if ($result->isValid()) {
                    return $this->redirect()->toRoute('home');
                }

                $error = 'Credenciais inválidas';
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
                    $this->userService->createUser($data);

                    $this->flashMessenger()->addSuccessMessage(
                        'Cadastro realizado com sucesso! Faça login para continuar.'
                    );
                    return $this->redirect()->toRoute('login');
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