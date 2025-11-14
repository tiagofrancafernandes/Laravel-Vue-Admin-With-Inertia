# Regras de Negócio

## 1. Gestão de Clientes

### 1.1. Cliente Anônimo

**Regra:** O sistema deve sempre ter um cliente "Anônimo" cadastrado.

**Implementação:**
- Criar via seeder na instalação
- `is_anonymous = true` e `name = 'Anônimo'`
- Não pode ser excluído
- Usado automaticamente quando nenhum cliente é selecionado

**Validações:**
- Não permitir edição do nome do cliente anônimo
- Não permitir exclusão do cliente anônimo
- Garantir que apenas um cliente tenha `is_anonymous = true`

### 1.2. Cadastro de Cliente

**Campos obrigatórios:**
- Nome

**Campos opcionais:**
- Email (único quando informado)
- Telefone
- Documento (CPF/CNPJ - único quando informado)
- Observações

**Validações:**
- Email deve ser válido se informado
- Telefone deve ter formato brasileiro se informado
- Documento deve ser CPF ou CNPJ válido se informado
- Nome deve ter no mínimo 3 caracteres

### 1.3. Cliente em Vendas

**Regra:** Toda venda deve ter um cliente associado.

**Comportamento:**
- Se nenhum cliente for selecionado, usar cliente "Anônimo"
- Ao selecionar cliente, carregar automaticamente:
  - Saldo disponível
  - Valor devido na caderneta
  - Últimas compras (opcional)

## 2. Gestão de Vendas

### 2.1. Campos da Venda

**Obrigatórios:**
- Valor total (`total_amount`)
- Pelo menos um método de pagamento
- Cliente (ou usa Anônimo)

**Opcionais:**
- Itens da venda (array JSON)
- Observações

### 2.2. Código da Venda

**Formato:** `VENDA-YYYYNNNN`
- `YYYY`: Ano atual
- `NNNN`: Número sequencial do ano (4 dígitos)

**Exemplos:**
- `VENDA-20250001`
- `VENDA-20250002`
- `VENDA-20251234`

**Geração:**
```php
// No modelo Sale ou Service
$year = date('Y');
$lastCode = Sale::whereYear('created_at', $year)
    ->latest('id')
    ->value('code');

if ($lastCode) {
    $number = intval(substr($lastCode, -4)) + 1;
} else {
    $number = 1;
}

$code = sprintf('VENDA-%s%04d', $year, $number);
```

### 2.3. Itens da Venda (Opcional)

**Estrutura JSON:**
```json
[
    {
        "name": "Nome do produto/serviço",
        "quantity": 2,
        "unit_price": 10.50,
        "subtotal": 21.00
    }
]
```

**Validações quando informado:**
- `name` obrigatório
- `quantity` > 0
- `unit_price` >= 0
- `subtotal` = `quantity` × `unit_price`
- Soma dos subtotais deve ser igual ao `total_amount` da venda

**Comportamento:**
- Se itens não forem informados, apenas registrar o valor total
- Itens são para detalhamento, não afetam lógica de pagamento

### 2.4. Status da Venda

**Valores possíveis:**
- `completed`: Venda finalizada com sucesso (padrão)
- `pending`: Venda em processamento (raro)
- `cancelled`: Venda cancelada

**Regras:**
- Venda criada sempre começa como `completed`
- Cancelamento deve estornar pagamentos e movimentações
- Venda cancelada não pode ser editada

## 3. Métodos de Pagamento

### 3.1. Métodos Padrão

| Código | Nome | Características |
|--------|------|-----------------|
| `cash` | Dinheiro | Aceita troco |
| `pix` | PIX | Pagamento instantâneo |
| `credit_card` | Cartão de Crédito | Pode ter parcelas |
| `debit_card` | Cartão de Débito | Pagamento à vista |
| `balance` | Saldo | Requer saldo do cliente |
| `tab` | Caderneta | Gera débito para o cliente |

### 3.2. Método: Dinheiro (cash)

**Regras:**
- Pode haver troco se valor pago > valor da compra
- Troco pode ser:
  - Devolvido ao cliente
  - Adicionado como saldo do cliente (se cliente não for Anônimo)

**Fluxo com troco:**
1. Cliente compra R$ 10,00
2. Paga R$ 20,00 em dinheiro
3. Sistema calcula troco: R$ 10,00
4. Sistema pergunta:
   - "Devolver R$ 10,00"
   - "Adicionar R$ 10,00 ao saldo do cliente"
5. Se adicionar ao saldo:
   - Registra `sale_payments`: amount = 10.00, metadata = `{"change_as_balance": 10.00}`
   - Cria registro em `client_ledger`: type = 'credit', amount = 10.00
   - Atualiza `client_balances`: balance_amount += 10.00

### 3.3. Método: Saldo (balance)

**Regras:**
- Requer cliente cadastrado (não funciona com Anônimo)
- Verifica saldo disponível antes de processar
- Debita do saldo do cliente

**Validações:**
- Cliente deve ter `balance_amount` >= valor a ser debitado
- Se saldo insuficiente, não permitir ou sugerir split de pagamento

**Fluxo de uso:**
1. Ao selecionar cliente, mostrar saldo disponível
2. Se método "Saldo" for selecionado, validar disponibilidade
3. Se OK:
   - Registra `sale_payments`
   - Cria registro em `client_ledger`: type = 'debit', amount = valor
   - Atualiza `client_balances`: balance_amount -= valor

### 3.4. Método: Caderneta (tab)

**Regras:**
- Cliente fica devendo o valor
- Gera débito na conta do cliente
- Não tem limite (negócio pode implementar depois)

**Validações:**
- Cliente não pode ser "Anônimo"
- Opcional: verificar se cliente já tem dívidas muito altas

**Fluxo de uso:**
1. Seleciona método "Caderneta"
2. Sistema registra:
   - `sale_payments`: amount = valor
   - `client_ledger`: type = 'tab_debit', amount = valor
   - Atualiza `client_balances`: tab_amount += valor

### 3.5. Adição de Saldo

**Quando:** Cliente adiciona crédito pré-pago.

**Fluxo:**
1. Cliente informa valor a adicionar
2. Cliente paga (dinheiro, PIX, cartão, etc.)
3. Sistema registra:
   - Não cria venda, apenas movimentação
   - `client_ledger`: type = 'credit', amount = valor, description = "Adição de saldo"
   - Atualiza `client_balances`: balance_amount += valor

**Interface:**
- Pode ser uma opção no perfil do cliente
- Ou um botão "Adicionar Saldo" na tela de vendas

### 3.6. Pagamento de Caderneta

**Quando:** Cliente paga parte ou total da dívida.

**Fluxo:**
1. Cliente informa valor a pagar
2. Cliente paga (dinheiro, PIX, cartão, etc.)
3. Sistema registra:
   - Não cria venda, apenas movimentação
   - `client_ledger`: type = 'tab_credit', amount = valor, description = "Pagamento de dívida"
   - Atualiza `client_balances`: tab_amount -= valor

**Validações:**
- Valor pago não pode ser maior que `tab_amount`
- Se valor pago = `tab_amount`, zera a dívida

## 4. Split de Pagamento

### 4.1. Conceito

**Definição:** Dividir o pagamento de uma venda entre múltiplos métodos.

**Exemplo:**
- Compra de R$ 100,00
- R$ 50,00 em dinheiro
- R$ 30,00 em PIX
- R$ 20,00 no saldo do cliente

### 4.2. Regra Principal

**A soma dos valores de todos os métodos deve ser exatamente igual ao valor total da venda.**

**Validação:**
```php
$totalPayments = $salePayments->sum('amount');
if ($totalPayments != $sale->total_amount) {
    throw new \Exception('Total dos pagamentos não corresponde ao valor da venda');
}
```

### 4.3. Cenários de Split

#### Cenário 1: Pagamento Simples
- Compra: R$ 50,00
- Pagamento: R$ 50,00 em PIX
- Resultado: 1 registro em `sale_payments`

#### Cenário 2: Split Básico
- Compra: R$ 100,00
- Pagamento: R$ 60,00 em dinheiro + R$ 40,00 no cartão
- Resultado: 2 registros em `sale_payments`

#### Cenário 3: Saldo Insuficiente + Complemento
- Compra: R$ 50,00
- Cliente tem R$ 30,00 de saldo
- Pagamento: R$ 30,00 do saldo + R$ 20,00 em dinheiro
- Resultado: 2 registros em `sale_payments`

#### Cenário 4: Falta de Dinheiro + Caderneta
- Compra: R$ 100,00
- Cliente tem apenas R$ 80,00
- Pagamento: R$ 80,00 em dinheiro + R$ 20,00 na caderneta
- Resultado: 2 registros em `sale_payments`

#### Cenário 5: Troco como Saldo + Caderneta
- Compra: R$ 15,00
- Cliente paga R$ 20,00 em dinheiro
- Troco de R$ 5,00 vira saldo
- Cliente pede para adicionar mais R$ 10,00 de saldo
- Mas só tinha R$ 20,00, faltam R$ 10,00
- Solução: R$ 15,00 na venda + R$ 5,00 troco vira saldo (total R$ 20,00 pago)
- Depois faz nova operação para adicionar R$ 10,00 ao saldo

### 4.4. Interface de Split

**UI sugerida:**
- Lista de métodos de pagamento
- Para cada método: campo de valor
- Indicador visual: "Pago: R$ X / Total: R$ Y"
- Botão "+" para adicionar mais métodos
- Validação em tempo real

## 5. Fluxos Completos

### 5.1. Fluxo: Criar Venda Simples

```
1. Atendente acessa "Nova Venda"
2. Informa valor total: R$ 50,00
3. Seleciona cliente (ou deixa Anônimo)
4. Seleciona método de pagamento: PIX
5. Clica em "Finalizar Venda"

Sistema:
6. Valida dados
7. Cria registro em `sales`
8. Cria registro em `sale_payments`
9. Retorna sucesso com código da venda
```

### 5.2. Fluxo: Venda com Saldo

```
1. Atendente acessa "Nova Venda"
2. Seleciona cliente "João Silva"
3. Sistema mostra: "Saldo disponível: R$ 100,00"
4. Informa valor total: R$ 30,00
5. Seleciona método: Saldo
6. Sistema valida: saldo suficiente
7. Clica em "Finalizar Venda"

Sistema:
8. Cria venda
9. Cria sale_payment com método "balance"
10. Cria client_ledger (type: debit, amount: 30)
11. Atualiza client_balances: balance_amount de 100 para 70
12. Retorna sucesso
```

### 5.3. Fluxo: Venda com Saldo Insuficiente + Dinheiro

```
1. Atendente acessa "Nova Venda"
2. Seleciona cliente "Maria Santos"
3. Sistema mostra: "Saldo disponível: R$ 20,00"
4. Informa valor total: R$ 50,00
5. Seleciona método: Saldo (R$ 20,00)
6. Sistema alerta: "Faltam R$ 30,00"
7. Atendente clica em "+ Adicionar Método"
8. Adiciona: Dinheiro (R$ 30,00)
9. Sistema valida: R$ 20 + R$ 30 = R$ 50 ✓
10. Clica em "Finalizar Venda"

Sistema:
11. Cria venda
12. Cria 2 sale_payments
13. Debita saldo: 20 → 0
14. Registra dinheiro: R$ 30
15. Retorna sucesso
```

### 5.4. Fluxo: Troco como Saldo

```
1. Atendente acessa "Nova Venda"
2. Seleciona cliente "Pedro Costa"
3. Informa valor total: R$ 15,00
4. Seleciona método: Dinheiro
5. Informa valor pago: R$ 20,00
6. Sistema calcula troco: R$ 5,00
7. Sistema pergunta: "Troco de R$ 5,00. Deseja:"
   - [ ] Devolver ao cliente
   - [x] Adicionar ao saldo do cliente
8. Atendente seleciona "Adicionar ao saldo"
9. Clica em "Finalizar Venda"

Sistema:
10. Cria venda (total: R$ 15)
11. Cria sale_payment (método: cash, amount: 15, metadata: {"change_as_balance": 5})
12. Cria client_ledger (type: credit, amount: 5, description: "Troco venda #...")
13. Atualiza client_balances: balance_amount += 5
14. Retorna sucesso
```

### 5.5. Fluxo: Venda na Caderneta

```
1. Atendente acessa "Nova Venda"
2. Seleciona cliente "Ana Lima"
3. Sistema mostra: "Dívida atual: R$ 50,00"
4. Informa valor total: R$ 25,00
5. Seleciona método: Caderneta
6. Sistema alerta: "Dívida ficará: R$ 75,00"
7. Clica em "Finalizar Venda"

Sistema:
8. Cria venda
9. Cria sale_payment (método: tab, amount: 25)
10. Cria client_ledger (type: tab_debit, amount: 25)
11. Atualiza client_balances: tab_amount de 50 para 75
12. Retorna sucesso
```

### 5.6. Fluxo: Adicionar Saldo (sem venda)

```
1. Atendente acessa perfil do cliente "Carlos Souza"
2. Clica em "Adicionar Saldo"
3. Informa valor: R$ 100,00
4. Cliente paga R$ 100,00 (PIX)
5. Clica em "Confirmar"

Sistema:
6. Valida pagamento
7. Cria client_ledger (type: credit, amount: 100, description: "Adição de saldo")
8. Atualiza client_balances: balance_amount += 100
9. Retorna sucesso
```

### 5.7. Fluxo: Pagar Caderneta

```
1. Atendente acessa perfil do cliente "Fernanda Dias"
2. Sistema mostra: "Dívida: R$ 150,00"
3. Clica em "Pagar Dívida"
4. Informa valor a pagar: R$ 80,00
5. Cliente paga R$ 80,00 (Dinheiro)
6. Clica em "Confirmar"

Sistema:
7. Valida valor <= tab_amount
8. Cria client_ledger (type: tab_credit, amount: 80, description: "Pagamento de dívida")
9. Atualiza client_balances: tab_amount de 150 para 70
10. Retorna sucesso
```

### 5.8. Fluxo: Criar Cliente durante Venda

```
1. Atendente está em "Nova Venda"
2. Clica no select de cliente
3. Clica em "+ Novo Cliente"
4. Modal abre sem sair da tela
5. Preenche dados:
   - Nome: Roberto Alves
   - Telefone: (11) 98765-4321
6. Clica em "Salvar"

Sistema:
7. Valida dados
8. Cria cliente
9. Fecha modal
10. Seleciona automaticamente o cliente recém-criado
11. Atendente continua a venda normalmente
```

## 6. Validações Críticas

### 6.1. Validações Financeiras

```php
// 1. Soma dos pagamentos = total da venda
Rule::custom(function ($attribute, $value, $fail) use ($totalAmount) {
    $sum = collect($value)->sum('amount');
    if ($sum != $totalAmount) {
        $fail('A soma dos pagamentos deve ser igual ao total da venda.');
    }
});

// 2. Saldo suficiente para débito
if ($paymentMethod->code === 'balance') {
    if ($clientBalance->balance_amount < $amount) {
        throw new \Exception('Saldo insuficiente.');
    }
}

// 3. Cliente não anônimo para saldo/caderneta
if (in_array($paymentMethod->code, ['balance', 'tab'])) {
    if ($client->is_anonymous) {
        throw new \Exception('Cliente anônimo não pode usar saldo ou caderneta.');
    }
}

// 4. Valor do pagamento da caderneta <= dívida
if ($paymentType === 'tab_payment') {
    if ($amount > $clientBalance->tab_amount) {
        throw new \Exception('Valor maior que a dívida atual.');
    }
}
```

### 6.2. Transações Atômicas

**Todas as operações financeiras devem usar transactions:**

```php
DB::transaction(function () use ($saleData, $payments) {
    // 1. Criar venda
    $sale = Sale::create($saleData);

    // 2. Criar pagamentos
    foreach ($payments as $payment) {
        SalePayment::create([
            'sale_id' => $sale->id,
            'payment_method_id' => $payment['method_id'],
            'amount' => $payment['amount'],
        ]);

        // 3. Processar movimentações
        $this->processPaymentMovement($sale, $payment);
    }
});
```

### 6.3. Auditoria

**Toda movimentação financeira deve gerar registro no ledger:**

```php
private function logLedger($clientId, $type, $amount, $description, $saleId = null)
{
    $client = Client::with('balance')->find($clientId);

    ClientLedger::create([
        'client_id' => $clientId,
        'sale_id' => $saleId,
        'type' => $type,
        'amount' => $amount,
        'balance_after' => $client->balance->balance_amount,
        'tab_after' => $client->balance->tab_amount,
        'description' => $description,
        'created_by' => auth()->id(),
    ]);
}
```

## 7. Casos Extremos

### 7.1. Venda de R$ 0,00

**Comportamento:** Permitir, mas exigir pelo menos 1 item ou observação.

**Uso:** Amostras grátis, brindes, etc.

### 7.2. Cliente com Saldo Negativo

**Não permitir.** Saldo sempre >= 0. Se ficar negativo por erro, criar alerta.

### 7.3. Múltiplos Atendentes Vendendo para o Mesmo Cliente

**Concorrência:** Usar locks otimistas ou pessimistas ao atualizar saldo.

```php
// Lock pessimista
$balance = ClientBalance::where('client_id', $clientId)
    ->lockForUpdate()
    ->first();

// Verificar e debitar
if ($balance->balance_amount >= $amount) {
    $balance->decrement('balance_amount', $amount);
}
```

### 7.4. Cancelamento de Venda

**Regras:**
- Estornar todos os pagamentos
- Reverter movimentações no ledger
- Atualizar saldo e caderneta do cliente
- Manter registro para auditoria (soft delete)

```php
DB::transaction(function () use ($sale) {
    // 1. Reverter pagamentos
    foreach ($sale->payments as $payment) {
        $this->reversePayment($sale, $payment);
    }

    // 2. Marcar como cancelada
    $sale->update(['status' => 'cancelled']);

    // 3. Soft delete
    $sale->delete();
});
```

## 8. Resumo de Regras Críticas

1. ✅ Toda venda deve ter um cliente (usar Anônimo se não informado)
2. ✅ Soma dos pagamentos = valor total da venda (sempre)
3. ✅ Verificar saldo antes de debitar
4. ✅ Cliente anônimo não pode ter saldo ou caderneta
5. ✅ Toda movimentação financeira gera registro no ledger
6. ✅ Usar transações para operações financeiras
7. ✅ Validar integridade dos dados antes de salvar
8. ✅ Tratar concorrência em operações de saldo
9. ✅ Soft delete para auditoria
10. ✅ Logs detalhados de todas as operações críticas
