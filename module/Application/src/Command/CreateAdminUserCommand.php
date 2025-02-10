<?php

declare(strict_types=1);

namespace Application\Command;

use Application\Entity\User;
use Application\Service\UserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateAdminUserCommand extends Command
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

    protected function configure(): void
    {
        $this->setName('app:create-admin')
            ->setDescription('Cria um usuário administrador')
            ->setHelp('Este comando permite criar um usuário com papel de administrador')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'E-mail do administrador')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Senha do administrador')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Nome do administrador');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        // Obtém o e-mail
        $email = $input->getOption('email');
        if (!$email) {
            $question = new Question('Digite o e-mail do administrador: ');
            $email = $helper->ask($input, $output, $question);
        }

        // Obtém a senha
        $password = $input->getOption('password');
        if (!$password) {
            $question = new Question('Digite a senha do administrador: ');
            $question->setHidden(true);
            $password = $helper->ask($input, $output, $question);
        }

        // Obtém o nome
        $name = $input->getOption('name');
        if (!$name) {
            $question = new Question('Digite o nome do administrador: ');
            $name = $helper->ask($input, $output, $question);
        }

        try {
            // Verifica se já existe um usuário com este e-mail
            if ($this->userService->getUserByEmail($email)) {
                $output->writeln('<error>Já existe um usuário com este e-mail.</error>');
                return Command::FAILURE;
            }

            // Cria o usuário admin
            $data = [
                'email' => $email,
                'password' => $password,
                'name' => $name,
                'role' => User::ROLE_ADMIN
            ];

            $user = $this->userService->createUser($data);
            
            // Adiciona verificação do papel após a criação
            if ($user->getRole() !== User::ROLE_ADMIN) {
                $output->writeln('<error>Erro: O usuário foi criado, mas não como administrador.</error>');
                return Command::FAILURE;
            }
            
            $output->writeln('<info>Usuário administrador criado com sucesso!</info>');
            $output->writeln(sprintf('ID: %d', $user->getId()));
            $output->writeln(sprintf('Nome: %s', $user->getName()));
            $output->writeln(sprintf('E-mail: %s', $user->getEmail()));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
} 