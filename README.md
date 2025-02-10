# Sistema de Agendamento de Cartório

Sistema web desenvolvido em PHP com Laminas Framework para gerenciamento de agendamentos de serviços cartorários.

## Requisitos

- Docker
- Docker Compose
- Git

## Instalação

1. Clone o repositório:
```bash
git clone [URL_DO_REPOSITORIO]
cd agendamento_cartorio
```

2. Copie o arquivo de ambiente:
```bash
cp .env.example .env
```

3. Inicie os containers Docker:
```bash
docker-compose up -d
```

4. Instale as dependências do PHP:
```bash
docker-compose exec app composer install
```

5. Execute as migrações do banco de dados:
```bash
docker-compose exec app php vendor/bin/doctrine-module migrations:migrate
```

6. Crie o usuário administrador:
```bash
docker-compose exec app php vendor/bin/laminas command:create-admin-user
```

## Acessando o Sistema

Após a instalação, você pode acessar:

- **Sistema Web**: http://localhost:8080
- **PHPMyAdmin**: http://localhost:8081
  - Servidor: mysql
  - Usuário: root
  - Senha: root

## Estrutura do Projeto

```
agendamento_cartorio/
├── config/                 # Configurações globais
├── data/                   # Cache e logs
├── docker/                 # Configurações do Docker
├── module/                 # Módulos da aplicação
│   └── Application/       # Módulo principal
├── public/                # Arquivos públicos
└── vendor/                # Dependências
```

## Funcionalidades

- Cadastro e autenticação de usuários
- Gerenciamento de serviços cartorários
- Agendamento de horários
- Painel administrativo
- Notificações por e-mail
- Relatórios e estatísticas

## Usuários do Sistema

O sistema possui três tipos de usuários:

1. **Administrador**
   - Acesso total ao sistema
   - Gerenciamento de usuários
   - Configuração de serviços

2. **Funcionário**
   - Visualização de agendamentos
   - Confirmação de horários
   - Atendimento aos clientes

3. **Cliente**
   - Agendamento de serviços
   - Visualização de histórico
   - Atualização de perfil

## Desenvolvimento

Para desenvolver novas funcionalidades:

1. Crie uma nova branch:
```bash
git checkout -b feature/nova-funcionalidade
```

2. Faça suas alterações e commit:
```bash
git add .
git commit -m "Descrição da alteração"
```

3. Envie para o repositório:
```bash
git push origin feature/nova-funcionalidade
```

## Comandos Úteis

- **Reiniciar containers**:
```bash
docker-compose restart
```

- **Logs dos containers**:
```bash
docker-compose logs -f
```

- **Acessar container da aplicação**:
```bash
docker-compose exec app bash
```

- **Limpar cache**:
```bash
docker-compose exec app php vendor/bin/laminas clear-cache
```

## Suporte

Em caso de dúvidas ou problemas:

1. Verifique os logs em `data/logs`
2. Consulte a documentação do Laminas Framework
3. Abra uma issue no repositório

## Licença

Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.
