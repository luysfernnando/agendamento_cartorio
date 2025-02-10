<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;

abstract class AbstractController extends AbstractActionController
{
    /**
     * Verifica se o usuário atual é um administrador
     */
    protected function isAdmin(): bool
    {
        $identity = $this->identity();
        return $identity && $identity->getRole() === 'admin';
    }

    /**
     * Verifica se o usuário está autenticado
     */
    protected function isAuthenticated(): bool
    {
        return (bool) $this->identity();
    }

    /**
     * Redireciona para a página de login se o usuário não estiver autenticado
     */
    protected function requireAuth(): ?\Laminas\Http\Response
    {
        if (!$this->isAuthenticated()) {
            $this->flashMessenger()->addWarningMessage('Você precisa estar logado para acessar esta página.');
            return $this->redirect()->toRoute('login');
        }
        return null;
    }

    /**
     * Redireciona para a página inicial se o usuário não for administrador
     */
    protected function requireAdmin(): ?\Laminas\Http\Response
    {
        if (!$this->isAdmin()) {
            $this->flashMessenger()->addErrorMessage('Acesso negado. Você precisa ser administrador para acessar esta página.');
            return $this->redirect()->toRoute('home');
        }
        return null;
    }
} 