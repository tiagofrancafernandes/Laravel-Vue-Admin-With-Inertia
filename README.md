# Sistema de Gestão de Vendas

Sistema completo para registro de vendas de estabelecimentos comerciais com suporte a múltiplos métodos de pagamento, gestão de saldo pré-pago e caderneta (fiado).

## Características Principais

- ✅ **Múltiplos Métodos de Pagamento:** Dinheiro, PIX, Cartão, Saldo e Caderneta
- ✅ **Split de Pagamento:** Divida uma venda em vários métodos
- ✅ **Saldo Pré-pago:** Cliente carrega créditos e usa nas compras
- ✅ **Caderneta (Fiado):** Cliente compra a prazo e paga depois
- ✅ **Gestão de Troco:** Troco pode virar saldo do cliente
- ✅ **Controle de Acesso:** 3 níveis (Super Admin, Atendente, Cliente)
- ✅ **Auditoria Completa:** Todas as movimentações registradas
- ✅ **Interface Responsiva:** Funciona em desktop, tablet e mobile
- ✅ **Cadastro Inline:** Crie clientes sem sair da tela de venda

## Stack Tecnológica

- **Backend:** Laravel 11
- **Frontend:** Vue 3 (Composition API) + Inertia.js
- **Autenticação:** Laravel Breeze
- **Estilização:** TailwindCSS
- **Banco de Dados:** MySQL 8.0+ / PostgreSQL 14+

## Requisitos

- PHP 8.2 ou superior
- Composer
- Node.js 18 ou superior
- MySQL 8.0+ ou PostgreSQL 14+

## Documentação Completa

A documentação está organizada em documentos especializados:

### 📋 Arquitetura e Planejamento

1. **[ARCHITECTURE.md](./ARCHITECTURE.md)**
   - Visão geral do sistema
   - Estrutura de camadas
   - Organização de pastas
   - Princípios e convenções

### 🗄️ Banco de Dados

2. **[docs/DATABASE.md](./docs/DATABASE.md)**
   - Estrutura completa de tabelas
   - Relacionamentos e constraints
   - Migrations e seeders
   - Índices e otimizações

### 💼 Regras de Negócio

3. **[docs/BUSINESS_RULES.md](./docs/BUSINESS_RULES.md)**
   - Gestão de clientes e vendas
   - Métodos de pagamento e regras
   - Split de pagamento e cenários
   - Fluxos completos de uso
   - Validações críticas

### 🛣️ Rotas e API

4. **[docs/ROUTES.md](./docs/ROUTES.md)**
   - Todas as rotas web e API
   - Request/Response de cada endpoint
   - Validações por rota
   - Controle de acesso
   - Exemplos de uso

### 🎨 Componentes Frontend

5. **[docs/COMPONENTS.md](./docs/COMPONENTS.md)**
   - Estrutura de componentes Vue
   - Pages e layouts
   - Componentes reutilizáveis
   - Composables e helpers
   - Padrões de responsividade

### 🔐 Permissões e Segurança

6. **[docs/PERMISSIONS.md](./docs/PERMISSIONS.md)**
   - Tipos de usuário
   - Policies e middleware
   - Matriz de permissões
   - Integração com frontend
   - Testes de autorização

### 🚀 Implementação

7. **[docs/IMPLEMENTATION_GUIDE.md](./docs/IMPLEMENTATION_GUIDE.md)**
   - Guia passo a passo
   - 8 fases de implementação
   - Do zero ao deploy
   - Checklist completo
   - Configurações de produção

## Início Rápido

### 1. Instalação

```bash
# Clone o repositório
git clone <repository-url>
cd sales-system

# Instale dependências
composer install
npm install

# Configure o ambiente
cp .env.example .env
php artisan key:generate

# Configure o banco de dados no .env
DB_CONNECTION=mysql
DB_DATABASE=sales_system
DB_USERNAME=root
DB_PASSWORD=

# Execute migrations e seeders
php artisan migrate --seed
```

### 2. Desenvolvimento

```bash
# Inicie o servidor
php artisan serve

# Em outro terminal, compile assets
npm run dev
```

Acesse: http://localhost:8000

### 3. Criar Primeiro Usuário (Super Admin)

```bash
php artisan tinker

>>> $user = new App\Models\User();
>>> $user->name = 'Admin';
>>> $user->email = 'admin@example.com';
>>> $user->password = bcrypt('password');
>>> $user->type = 'super_admin';
>>> $user->save();
```

## Estrutura do Projeto

```
sales-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Policies/
│   └── Services/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── js/
│   │   ├── Components/
│   │   ├── Pages/
│   │   ├── Composables/
│   │   └── Utils/
│   └── views/
├── routes/
│   ├── web.php
│   └── api.php
├── tests/
│   ├── Feature/
│   └── Unit/
└── docs/
    ├── DATABASE.md
    ├── BUSINESS_RULES.md
    ├── ROUTES.md
    ├── COMPONENTS.md
    ├── PERMISSIONS.md
    └── IMPLEMENTATION_GUIDE.md
```

## Principais Funcionalidades

### Para Atendentes

- **Registrar Vendas**
  - Cadastro rápido com valor e método de pagamento
  - Detalhamento opcional de itens
  - Split de pagamento entre vários métodos
  - Seleção ou criação inline de cliente

- **Gerenciar Clientes**
  - Cadastro completo com dados opcionais
  - Adicionar saldo pré-pago
  - Receber pagamento de caderneta
  - Visualizar histórico de compras

- **Dashboard**
  - Vendas e faturamento do dia
  - Clientes com dívidas
  - Vendas recentes

### Para Clientes

- **Visualizar Compras**
  - Histórico completo de vendas
  - Detalhes de cada compra
  - Filtros por data

- **Acompanhar Finanças**
  - Saldo disponível
  - Valor devido na caderneta
  - Histórico de movimentações

### Para Super Admin

- **Tudo que Atendente faz, mais:**
  - Gerenciar usuários do sistema
  - Cancelar vendas
  - Gerenciar métodos de pagamento
  - Acessar relatórios completos

## Conceitos Principais

### Cliente Anônimo

Toda venda deve ter um cliente. Se nenhum for informado, usa-se automaticamente o cliente "Anônimo" (criado via seeder).

### Saldo Pré-pago

Cliente adiciona créditos que ficam disponíveis para usar em compras futuras. Funciona como um "cartão de presente".

### Caderneta (Fiado)

Cliente compra a prazo, ficando com dívida registrada no sistema. Pode pagar parcial ou totalmente quando quiser.

### Split de Pagamento

Uma única venda pode ter múltiplos métodos de pagamento. Exemplo: R$ 50 em dinheiro + R$ 30 no PIX + R$ 20 no saldo.

### Troco como Saldo

Se cliente paga mais que o valor da compra em dinheiro, o troco pode ser devolvido ou adicionado ao saldo dele.

## Testes

```bash
# Executar todos os testes
php artisan test

# Executar com cobertura
php artisan test --coverage

# Executar testes específicos
php artisan test --filter SaleTest
```

## Deploy

Consulte o [Guia de Implementação](./docs/IMPLEMENTATION_GUIDE.md#fase-8-deploy) para instruções detalhadas de deploy em produção.

### Checklist Rápido

- [ ] Configurar servidor (PHP, MySQL, Nginx)
- [ ] Clonar repositório
- [ ] Instalar dependências (`composer install --no-dev`)
- [ ] Configurar .env de produção
- [ ] Executar migrations (`php artisan migrate --force`)
- [ ] Executar seeders obrigatórios
- [ ] Compilar assets (`npm run build`)
- [ ] Configurar cache (`php artisan optimize`)
- [ ] Configurar SSL
- [ ] Configurar backups automáticos

## Segurança

- ✅ Validação de dados no cliente e servidor
- ✅ Autorização granular com Policies
- ✅ Proteção CSRF automática (Laravel)
- ✅ Senhas criptografadas com bcrypt
- ✅ Soft deletes para auditoria
- ✅ Logs de operações críticas
- ✅ Rate limiting em rotas sensíveis
- ✅ Transações atômicas para operações financeiras

## Contribuindo

1. Leia toda a documentação antes de começar
2. Siga os padrões estabelecidos
3. Escreva testes para novas funcionalidades
4. Mantenha a documentação atualizada
5. Faça commits descritivos

## Licença

[Definir licença apropriada]

## Suporte

Para dúvidas ou problemas:

1. Consulte a documentação completa em `/docs`
2. Verifique os exemplos no código
3. Abra uma issue no GitHub

---

## 📚 Índice da Documentação

| Documento | Descrição | Link |
|-----------|-----------|------|
| Arquitetura | Visão geral e organização | [ARCHITECTURE.md](./ARCHITECTURE.md) |
| Banco de Dados | Tabelas e relacionamentos | [DATABASE.md](./docs/DATABASE.md) |
| Regras de Negócio | Fluxos e validações | [BUSINESS_RULES.md](./docs/BUSINESS_RULES.md) |
| Rotas | Endpoints e APIs | [ROUTES.md](./docs/ROUTES.md) |
| Componentes | Frontend Vue/Inertia | [COMPONENTS.md](./docs/COMPONENTS.md) |
| Permissões | Autorização e segurança | [PERMISSIONS.md](./docs/PERMISSIONS.md) |
| Implementação | Guia passo a passo | [IMPLEMENTATION_GUIDE.md](./docs/IMPLEMENTATION_GUIDE.md) |

---

**Desenvolvido com ❤️ usando Laravel, Vue e Inertia.js**
