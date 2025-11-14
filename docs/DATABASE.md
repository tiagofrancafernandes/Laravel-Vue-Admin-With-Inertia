# Estrutura do Banco de Dados

## Diagrama de Relacionamentos

```
users (1) ----< (N) sales
clients (1) ----< (N) sales
clients (1) ---- (1) client_balances
clients (1) ----< (N) client_ledger
sales (1) ----< (N) sale_payments
payment_methods (1) ----< (N) sale_payments
sales (1) ----< (N) client_ledger
```

## Tabelas

### users

Estende a tabela padrão do Laravel Breeze com campo `type`.

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    type ENUM('super_admin', 'attendant', 'client') DEFAULT 'client',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    INDEX idx_users_type (type),
    INDEX idx_users_email (email)
);
```

**Campos:**
- `type`: Define o tipo de usuário no sistema
  - `super_admin`: Acesso total ao sistema
  - `attendant`: Pode gerenciar vendas e clientes
  - `client`: Visualiza apenas seus próprios dados

### clients

Armazena informações dos clientes.

```sql
CREATE TABLE clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL UNIQUE,
    phone VARCHAR(20) NULL,
    document VARCHAR(20) NULL UNIQUE COMMENT 'CPF/CNPJ',
    is_anonymous BOOLEAN DEFAULT FALSE,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    INDEX idx_clients_name (name),
    INDEX idx_clients_email (email),
    INDEX idx_clients_phone (phone),
    INDEX idx_clients_document (document),
    INDEX idx_clients_is_anonymous (is_anonymous)
);
```

**Campos:**
- `is_anonymous`: Marca o cliente "Anônimo" padrão
- `document`: CPF ou CNPJ opcional
- `notes`: Observações sobre o cliente

**Regras:**
- Deve existir sempre um cliente com `is_anonymous = true` e `name = 'Anônimo'`
- Email e document devem ser únicos quando informados

### sales

Registra as vendas realizadas.

```sql
CREATE TABLE sales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL COMMENT 'Código único da venda',
    user_id BIGINT UNSIGNED NOT NULL COMMENT 'Atendente que registrou',
    client_id BIGINT UNSIGNED NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('completed', 'cancelled', 'pending') DEFAULT 'completed',
    items JSON NULL COMMENT 'Array de itens da venda',
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,

    INDEX idx_sales_code (code),
    INDEX idx_sales_user_id (user_id),
    INDEX idx_sales_client_id (client_id),
    INDEX idx_sales_status (status),
    INDEX idx_sales_created_at (created_at)
);
```

**Campos:**
- `code`: Código único gerado automaticamente (ex: `VENDA-20250001`)
- `total_amount`: Valor total da venda
- `status`: Estado da venda
- `items`: Estrutura JSON opcional dos itens

**Estrutura do campo items (JSON):**
```json
[
    {
        "name": "Produto A",
        "quantity": 2,
        "unit_price": 10.50,
        "subtotal": 21.00
    },
    {
        "name": "Produto B",
        "quantity": 1,
        "unit_price": 15.00,
        "subtotal": 15.00
    }
]
```

### payment_methods

Métodos de pagamento disponíveis no sistema.

```sql
CREATE TABLE payment_methods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL COMMENT 'Código único: cash, pix, credit_card, balance, tab',
    is_active BOOLEAN DEFAULT TRUE,
    requires_client_balance BOOLEAN DEFAULT FALSE COMMENT 'Se true, requer saldo do cliente',
    is_credit BOOLEAN DEFAULT FALSE COMMENT 'Se true, é crédito (caderneta)',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_payment_methods_code (code),
    INDEX idx_payment_methods_is_active (is_active)
);
```

**Campos:**
- `code`: Identificador único do método
  - `cash`: Dinheiro
  - `pix`: PIX
  - `credit_card`: Cartão de Crédito
  - `debit_card`: Cartão de Débito
  - `balance`: Saldo pré-pago
  - `tab`: Caderneta (fiado)
- `requires_client_balance`: Se true, verifica saldo antes de processar
- `is_credit`: Se true, gera débito na caderneta do cliente

**Dados iniciais (Seeder):**
```php
[
    ['name' => 'Dinheiro', 'code' => 'cash'],
    ['name' => 'PIX', 'code' => 'pix'],
    ['name' => 'Cartão de Crédito', 'code' => 'credit_card'],
    ['name' => 'Cartão de Débito', 'code' => 'debit_card'],
    ['name' => 'Saldo', 'code' => 'balance', 'requires_client_balance' => true],
    ['name' => 'Caderneta', 'code' => 'tab', 'is_credit' => true],
]
```

### sale_payments

Registra os pagamentos de uma venda (suporta split de pagamento).

```sql
CREATE TABLE sale_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_id BIGINT UNSIGNED NOT NULL,
    payment_method_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    metadata JSON NULL COMMENT 'Dados adicionais do pagamento',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE RESTRICT,

    INDEX idx_sale_payments_sale_id (sale_id),
    INDEX idx_sale_payments_payment_method_id (payment_method_id)
);
```

**Campos:**
- `metadata`: Informações extras como número de parcelas, troco devolvido, etc.

**Estrutura do campo metadata (JSON):**
```json
{
    "installments": 3,
    "change_returned": 5.00,
    "change_as_balance": 5.00,
    "reference": "Autorização 123456"
}
```

**Validação:**
- A soma de `amount` de todos os `sale_payments` deve ser igual ao `total_amount` da venda

### client_balances

Saldo atual de cada cliente.

```sql
CREATE TABLE client_balances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED UNIQUE NOT NULL,
    balance_amount DECIMAL(10, 2) DEFAULT 0.00,
    tab_amount DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Valor devido na caderneta',
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,

    INDEX idx_client_balances_client_id (client_id)
);
```

**Campos:**
- `balance_amount`: Saldo disponível (crédito pré-pago)
- `tab_amount`: Valor total devido na caderneta (sempre >= 0)

**Regras:**
- `balance_amount` pode ser >= 0
- `tab_amount` sempre >= 0
- Atualizado automaticamente via triggers ou eventos

### client_ledger

Registro histórico de todas as movimentações financeiras do cliente (auditoria).

```sql
CREATE TABLE client_ledger (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    sale_id BIGINT UNSIGNED NULL,
    type ENUM('credit', 'debit', 'tab_credit', 'tab_debit') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    balance_after DECIMAL(10, 2) NULL COMMENT 'Saldo após a operação',
    tab_after DECIMAL(10, 2) NULL COMMENT 'Caderneta após a operação',
    description TEXT NOT NULL,
    created_by BIGINT UNSIGNED NULL COMMENT 'Usuário que registrou',
    created_at TIMESTAMP NULL,

    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_client_ledger_client_id (client_id),
    INDEX idx_client_ledger_sale_id (sale_id),
    INDEX idx_client_ledger_type (type),
    INDEX idx_client_ledger_created_at (created_at)
);
```

**Campos:**
- `type`: Tipo de movimentação
  - `credit`: Crédito no saldo (adição de saldo)
  - `debit`: Débito no saldo (uso do saldo)
  - `tab_credit`: Crédito na caderneta (pagamento de dívida)
  - `tab_debit`: Débito na caderneta (nova dívida)
- `balance_after`: Snapshot do saldo após a operação
- `tab_after`: Snapshot da caderneta após a operação
- `description`: Descrição da movimentação

**Exemplos de registros:**
```
Cliente adiciona R$ 50 de saldo:
  type: credit, amount: 50.00, description: "Adição de saldo"

Cliente usa R$ 20 do saldo em uma venda:
  type: debit, amount: 20.00, sale_id: 123, description: "Pagamento venda #VENDA-20250001"

Cliente fica devendo R$ 15:
  type: tab_debit, amount: 15.00, sale_id: 124, description: "Crédito venda #VENDA-20250002"

Cliente paga R$ 10 da caderneta:
  type: tab_credit, amount: 10.00, description: "Pagamento de dívida"
```

## Migrations - Ordem de Criação

1. `2024_01_01_000001_add_type_to_users_table.php`
2. `2024_01_01_000002_create_clients_table.php`
3. `2024_01_01_000003_create_client_balances_table.php`
4. `2024_01_01_000004_create_payment_methods_table.php`
5. `2024_01_01_000005_create_sales_table.php`
6. `2024_01_01_000006_create_sale_payments_table.php`
7. `2024_01_01_000007_create_client_ledger_table.php`

## Seeders

### DatabaseSeeder

```php
public function run(): void
{
    $this->call([
        PaymentMethodSeeder::class,
        DefaultClientSeeder::class,
        SuperAdminSeeder::class, // Apenas em desenvolvimento
    ]);
}
```

### PaymentMethodSeeder

Cria os métodos de pagamento padrão.

### DefaultClientSeeder

Cria o cliente "Anônimo" padrão.

```php
Client::create([
    'name' => 'Anônimo',
    'is_anonymous' => true,
]);
```

### SuperAdminSeeder (apenas dev)

Cria usuário super admin para desenvolvimento.

## Índices e Performance

**Índices criados:**
- Primary keys em todas as tabelas
- Foreign keys com índices
- Campos de busca frequente (email, phone, document)
- Campos de filtro (type, status, is_active)
- Campos de ordenação (created_at)

**Recomendações:**
- Use `EXPLAIN` para analisar queries lentas
- Considere índices compostos se houver filtros múltiplos frequentes
- Monitor queries N+1 com Laravel Debugbar

## Constraints e Validações

**A nível de banco:**
- Foreign keys com `ON DELETE RESTRICT` para vendas e clientes (não permite exclusão se houver dependências)
- Foreign keys com `ON DELETE CASCADE` para registros dependentes (ledger, balances)
- `UNIQUE` constraints em campos únicos
- `NOT NULL` em campos obrigatórios

**A nível de aplicação:**
- Validação de saldo antes de debitar
- Validação de soma de pagamentos = total da venda
- Transações para operações críticas
- Soft deletes para auditoria

## Backup e Auditoria

**Campos de auditoria em todas as tabelas:**
- `created_at`: Data de criação
- `updated_at`: Data de última atualização
- `deleted_at`: Data de exclusão (soft delete)

**Tabela de auditoria completa:**
- `client_ledger`: Registra todas as movimentações financeiras

**Recomendações:**
- Backup diário do banco de dados
- Retenção de soft deletes por no mínimo 1 ano
- Logs de alterações em vendas (considerar Laravel Auditing)
