# AppMaker Actions System

Sistema reutilizável de actions para o AppMaker que suporta diferentes tipos de ações com modals de confirmação.

## 📦 Componentes

### ConfirmDeleteModal.vue
Modal de confirmação para ações destrutivas (delete).

**Props:**
- `show: boolean` - Controla visibilidade
- `title: string` - Título da modal (default: "Confirm Delete")
- `message: string` - Mensagem de confirmação
- `itemName: string` - Nome do item sendo deletado (opcional)
- `confirmButtonText: string` - Texto do botão de confirmação (default: "Delete")
- `loading: boolean` - Estado de loading

**Events:**
- `@close` - Emitido ao fechar modal
- `@confirm` - Emitido ao confirmar ação

### ActionLink.vue
Renderiza link de navegação usando Inertia.js.

**Props:**
- `action: Object` - Configuração da action
- `record: Object` - Registro atual
- `resource: String` - Nome do resource (ex: "users")

### ActionGroup.vue
Coordenador de múltiplas actions. Gerencia renderização e modal de confirmação.

**Props:**
- `actions: Array` - Array de configurações de actions
- `record: Object` - Registro atual
- `resource: String` - Nome do resource
- `itemNameKey: String` - Key para nome do item (default: "name")

**Events:**
- `@action` - Emitido após execução de action `(action, recordId)`

### TableActions.vue
Componente refatorado para usar o sistema de actions.

**Props:**
- `actions: Array` - Actions customizadas (opcional)
- `record: Object` - Registro atual
- `resource: String` - Nome do resource
- `itemNameKey: String` - Key para nome do item (default: "name")

**Behavior:**
- Se `actions` não for passado, renderiza View/Edit padrão (backward compatible)
- Se `actions` for passado, usa ActionGroup

## 📝 Action Configuration

```javascript
const actions = [
    {
        type: 'link',          // Tipo: 'link' | 'delete' | 'button' | 'emit'
        name: 'view',          // Identificador único
        label: 'View',         // Texto exibido
        route: 'users.show',   // Nome da rota
        color: 'blue',         // Cor: 'blue' | 'green' | 'red' | 'yellow' | 'gray'
    },
    {
        type: 'delete',
        name: 'delete',
        label: 'Delete',
        route: 'users.destroy',
        color: 'red',
        confirmTitle: 'Delete User',           // Título da modal
        confirmMessage: 'Are you sure?',       // Mensagem da modal
        condition: (record) => record.id !== currentUserId, // Condição para exibir
    },
];
```

## 🎯 Tipos de Actions

### 1. Link Action (`type: 'link'`)
Navegação usando Inertia Link.

```javascript
{
    type: 'link',
    name: 'edit',
    label: 'Edit',
    route: 'users.edit',
    color: 'green',
}
```

### 2. Delete Action (`type: 'delete'`)
Ação de delete com modal de confirmação.

```javascript
{
    type: 'delete',
    name: 'delete',
    label: 'Delete',
    route: 'users.destroy',
    color: 'red',
    confirmTitle: 'Delete User',
    confirmMessage: 'Are you sure you want to delete this user?',
}
```

### 3. Button Action (`type: 'button'` ou `type: 'emit'`)
Botão que emite evento.

```javascript
{
    type: 'button',
    name: 'custom-action',
    label: 'Custom',
    color: 'yellow',
    onClick: (record) => {
        console.log('Clicked', record);
    },
}
```

## 💡 Uso Básico

### Em uma tabela (Users/Index.vue)

```vue
<script setup>
import TableActions from '@/Components/AppMaker/Table/TableActions.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const userActions = [
    {
        type: 'link',
        name: 'view',
        label: 'View',
        route: 'users.show',
        color: 'blue',
    },
    {
        type: 'link',
        name: 'edit',
        label: 'Edit',
        route: 'users.edit',
        color: 'green',
    },
    {
        type: 'delete',
        name: 'delete',
        label: 'Delete',
        route: 'users.destroy',
        color: 'red',
        confirmTitle: 'Delete User',
        confirmMessage: 'Are you sure you want to delete this user?',
        condition: (user) => user.id !== currentUser.value.id,
    },
];

const handleAction = (action, userId) => {
    console.log(`Action ${action.name} on user ${userId}`);
};
</script>

<template>
    <TableActions
        :actions="userActions"
        :record="user"
        resource="users"
        @action="handleAction"
    />
</template>
```

## 🎨 Features

✅ **Dark Mode** - Suporte completo a modo escuro
✅ **Loading States** - Estados de loading durante ações
✅ **Toast Feedback** - Notificações de sucesso/erro
✅ **Acessibilidade** - ESC fecha modal, foco capturado
✅ **Responsivo** - Modal responsiva
✅ **Conditional Actions** - Actions com condições
✅ **Backward Compatible** - Mantém comportamento padrão

## 🔄 Backward Compatibility

TableActions mantém compatibilidade com código existente:

```vue
<!-- Sem actions configuradas = comportamento padrão (View, Edit) -->
<TableActions :record="user" resource="users" />

<!-- Com actions customizadas -->
<TableActions :actions="userActions" :record="user" resource="users" />
```

## 🚀 Próximos Passos

- [ ] Adicionar suporte a ícones personalizados
- [ ] Implementar PageActions para toolbars
- [ ] Bulk actions
- [ ] Dropdown de actions para mobile
- [ ] Modal customizadas (`type: 'modal'`)
- [ ] Permissões avançadas
