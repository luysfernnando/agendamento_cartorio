<?php

declare(strict_types=1);

namespace Application\View\Helper;

use Laminas\Authentication\AuthenticationService;
use Laminas\View\Helper\AbstractHelper;

class IsAdmin extends AbstractHelper
{
    private AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(): bool
    {
        if (!$this->authService->hasIdentity()) {
            return false;
        }

        $user = $this->authService->getIdentity();
        return $user->getRole() === 'admin';
    }
} 