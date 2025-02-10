<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Service\UserService;
use Laminas\Authentication\AuthenticationService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class ProfileController extends AbstractActionController
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

    public function indexAction()
    {
        if (!$this->authService->hasIdentity()) {
            return $this->redirect()->toRoute('login');
        }

        $user = $this->userService->getUserById($this->authService->getIdentity()->getId());

        return new ViewModel([
            'user' => $user
        ]);
    }
} 