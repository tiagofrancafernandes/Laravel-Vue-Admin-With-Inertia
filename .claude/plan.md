# Plano: Sistema de Actions AppMaker com Modal de Confirmação

## 📋 Visão Geral

Implementar um sistema robusto e reutilizável de actions para o AppMaker que suporte diferentes tipos de ações (delete, edit, link, modal, button, custom) com confirmação via modal para ações destrutivas.

## 🎯 Objetivos

1. **Criar modal de confirmação** para deleção de usuários
2. **Refatorar TableActions** para suportar diferentes tipos de actions
3. **Criar sistema genérico de Actions** reutilizável em Pages e Tables
4. **Implementar actions configuráveis** via props/config
5. **Manter compatibilidade** com código existente

## 🏗️ Arquitetura Proposta

### 1. Estrutura de Diretórios

```
resources/js/Components/AppMaker/
├── Actions/
│   ├── ActionButton.vue          # Botão genérico de ação
│   ├── ActionLink.vue            # Link de ação (Inertia)
│   ├── ActionModal.vue           # Wrapper para ações com modal
│   ├── ConfirmDeleteModal.vue    # Modal específico de confirmação delete
│   └── ActionGroup.vue           # Agrupador de múltiplas actions
├── Table/
│   ├── TableActions.vue          # Refatorado para usar novo sistema
│   └── ...
└── Pages/
    ├── PageActions.vue           # Actions para páginas (toolbar, etc)
    └── ...
```

### 2. Tipos de Actions Suportadas

```typescript
// Tipos de actions que serão suportadas
type ActionType =
    | 'link'          // Inertia Link (view, edit)
    | 'button'        // Botão simples (emite evento)
    | 'delete'        // Delete com confirmação via modal
    | 'modal'         // Abre modal customizada
    | 'emit'          // Emite evento customizado
    | 'route'         // Redireciona para rota
    | 'custom'        // Componente customizado

interface Action {
    type: ActionType;
    label: string;
    name: string;
    icon?: string;
    variant?: 'primary' | 'secondary' | 'danger' | 'success' | 'ghost';
    color?: string; // Para compatibilidade com código existente

    // Configurações específicas por tipo
    route?: string;           // Para type: 'link' | 'route'
    method?: string;          // Para type: 'link' (get, post, delete, etc)
    confirmMessage?: string;  // Para type: 'delete'
    confirmTitle?: string;    // Para type: 'delete'
    modalComponent?: any;     // Para type: 'modal'
    permission?: string;      // Permissão necessária
    condition?: (record: any) => boolean; // Condição para mostrar

    // Callbacks
    onClick?: (record: any) => void;
    onSuccess?: (response: any) => void;
    onError?: (error: any) => void;
}
```

### 3. Componentes a Criar

#### 3.1 ConfirmDeleteModal.vue
```vue
<!-- Modal reutilizável de confirmação de deleção -->
<template>
  <Modal :show="show" @close="emit('close')">
    <div class="p-6">
      <div class="flex items-center gap-4 mb-4">
        <div class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900/20
                    rounded-full flex items-center justify-center">
          <TrashIcon class="w-6 h-6 text-red-600 dark:text-red-400" />
        </div>
        <div>
          <h3 class="text-lg font-semibold">{{ title }}</h3>
          <p class="text-gray-600 dark:text-gray-400">{{ message }}</p>
        </div>
      </div>

      <div class="flex justify-end gap-3">
        <Button variant="ghost" @click="emit('close')">
          Cancel
        </Button>
        <Button
          variant="danger"
          :loading="loading"
          @click="emit('confirm')"
        >
          Delete
        </Button>
      </div>
    </div>
  </Modal>
</template>
```

**Props:**
- `show: boolean` - Controla visibilidade
- `title: string` - Título da modal
- `message: string` - Mensagem de confirmação
- `loading: boolean` - Estado de loading durante delete
- `itemName?: string` - Nome do item sendo deletado (opcional)

**Emits:**
- `close` - Fecha modal
- `confirm` - Confirma a ação

#### 3.2 ActionButton.vue
```vue
<!-- Botão de ação genérico -->
<template>
  <Button
    :variant="action.variant || 'ghost'"
    :size="size"
    :disabled="disabled || loading"
    :loading="loading"
    @click="handleClick"
  >
    <component :is="action.icon" v-if="action.icon" class="w-4 h-4 mr-2" />
    {{ action.label }}
  </Button>
</template>
```

**Funcionalidade:**
- Renderiza botão baseado em configuração de action
- Suporta ícones
- Gerencia estado de loading
- Emite eventos

#### 3.3 ActionLink.vue
```vue
<!-- Link de ação usando Inertia -->
<template>
  <Link
    :href="computedHref"
    :method="action.method || 'get'"
    :as="action.method !== 'get' ? 'button' : 'a'"
    :class="linkClasses"
  >
    <component :is="action.icon" v-if="action.icon" class="w-4 h-4 mr-1" />
    {{ action.label }}
  </Link>
</template>
```

**Funcionalidade:**
- Renderiza Inertia Link
- Suporta diferentes métodos HTTP
- Calcula href dinamicamente com record.id

#### 3.4 ActionGroup.vue
```vue
<!-- Agrupador de múltiplas actions -->
<template>
  <div class="flex items-center gap-2">
    <component
      :is="getActionComponent(action)"
      v-for="action in visibleActions"
      :key="action.name"
      :action="action"
      :record="record"
      :resource="resource"
      @action="handleAction"
    />

    <!-- Modal de confirmação de delete -->
    <ConfirmDeleteModal
      :show="showDeleteModal"
      :title="deleteAction?.confirmTitle || 'Confirm Delete'"
      :message="deleteAction?.confirmMessage || 'Are you sure?'"
      :loading="deleteLoading"
      @close="showDeleteModal = false"
      @confirm="confirmDelete"
    />
  </div>
</template>
```

**Funcionalidade:**
- Renderiza múltiplas actions baseado em config
- Filtra actions por condição/permissão
- Gerencia modal de confirmação delete
- Emite eventos para parent

#### 3.5 TableActions.vue (Refatorado)
```vue
<!-- Refatoração do TableActions existente -->
<template>
  <div class="flex items-center justify-end gap-2">
    <!-- Actions padrão (View, Edit) se não configurado -->
    <template v-if="!actions || actions.length === 0">
      <ActionLink
        :action="{ type: 'link', label: 'View', route: `${resource}.show` }"
        :record="record"
        :resource="resource"
      />
      <ActionLink
        :action="{ type: 'link', label: 'Edit', route: `${resource}.edit` }"
        :record="record"
        :resource="resource"
      />
    </template>

    <!-- Actions customizadas -->
    <ActionGroup
      v-else
      :actions="actions"
      :record="record"
      :resource="resource"
      @action="emit('action', $event)"
    />
  </div>
</template>
```

**Features:**
- Backward compatible (mantém View/Edit padrão)
- Suporta actions customizadas via props
- Usa novo sistema de ActionGroup

### 4. Integração com Users/Index.vue

#### 4.1 Configuração de Actions

```vue
<script setup>
// ... imports

const userActions = [
    {
        type: 'link',
        name: 'view',
        label: 'View',
        route: 'users.show',
        variant: 'ghost',
        color: 'blue', // Para cores customizadas
    },
    {
        type: 'link',
        name: 'edit',
        label: 'Edit',
        route: 'users.edit',
        variant: 'ghost',
        color: 'green',
    },
    {
        type: 'delete',
        name: 'delete',
        label: 'Delete',
        route: 'users.destroy',
        variant: 'ghost',
        color: 'red',
        confirmTitle: 'Delete User',
        confirmMessage: 'Are you sure you want to delete this user? This action cannot be undone.',
        condition: (user) => user.id !== currentUser.id, // Não deletar a si mesmo
    },
];

const handleAction = (action, userId) => {
    // Callback após ação (opcional)
    console.log(`Action ${action.name} on user ${userId}`);
};
</script>

<template>
  <!-- Na tabela -->
  <TableActions
    :actions="userActions"
    :record="user"
    resource="users"
    @action="handleAction"
  />
</template>
```

### 5. Fluxo de Deleção

```
1. User clica em "Delete"
   ↓
2. ActionGroup detecta type: 'delete'
   ↓
3. Abre ConfirmDeleteModal
   ↓
4. User confirma
   ↓
5. router.delete(route) com loading state
   ↓
6. Sucesso: Toast + atualização da lista
   Erro: Toast de erro
```

## 🎨 Features Importantes

### 1. Dark Mode Support
- Todos os componentes devem suportar dark mode
- Usar classes `dark:` do Tailwind

### 2. Loading States
- Botões mostram loading durante ação
- Modal de delete mostra loading
- Tabela não trava durante delete

### 3. Feedback Visual
- Toast de sucesso após delete
- Toast de erro se falhar
- Animações suaves nas modais

### 4. Acessibilidade
- Modais capturam foco
- ESC fecha modal
- Feedback para screen readers

### 5. Responsividade
- Actions colapsam em dropdown em mobile (futuro)
- Modais responsivas

## 📝 Checklist de Implementação

### Fase 1: Modal de Confirmação (MVP)
- [ ] Criar `ConfirmDeleteModal.vue`
- [ ] Adicionar ao `Users/Index.vue`
- [ ] Implementar lógica de delete com confirmação
- [ ] Adicionar toast de feedback
- [ ] Testar em modo claro e escuro

### Fase 2: Sistema de Actions Básico
- [ ] Criar `ActionButton.vue`
- [ ] Criar `ActionLink.vue`
- [ ] Criar `ActionGroup.vue`
- [ ] Refatorar `TableActions.vue`
- [ ] Testar com Users

### Fase 3: Actions Avançadas
- [ ] Suportar ícones nas actions
- [ ] Implementar `PageActions.vue` para toolbars
- [ ] Adicionar suporte a bulk actions
- [ ] Implementar permissões
- [ ] Documentar sistema de actions

### Fase 4: Expansão
- [ ] Dropdown de actions para mobile
- [ ] Modal customizadas (type: 'modal')
- [ ] Actions condicionais avançadas
- [ ] Testes unitários

## 🔄 Compatibilidade

### Backward Compatibility
- `TableActions` mantém comportamento padrão se `actions` não for passado
- `color` prop mantido para compatibilidade (mapeia para `variant`)
- Sistema atual de eventos preservado

### Migration Path
```vue
<!-- Antes -->
<TableActions :actions="[]" :record="user" resource="users" />
<!-- View e Edit renderizados automaticamente -->

<!-- Depois (sem breaking changes) -->
<TableActions :actions="userActions" :record="user" resource="users" />
<!-- Actions customizadas -->
```

## 🎯 Benefícios

1. **Reutilização**: Actions podem ser usadas em tabelas, páginas, cards, etc
2. **Consistência**: UI/UX uniforme em todo o app
3. **Manutenibilidade**: Lógica centralizada
4. **Extensibilidade**: Fácil adicionar novos tipos de actions
5. **Configurabilidade**: Actions via props, não hard-coded
6. **Type Safety**: TypeScript para actions config
7. **Acessibilidade**: Modais e interações acessíveis

## 📚 Exemplos de Uso Futuro

```vue
<!-- Em uma página de detalhes -->
<PageActions :actions="pageActions" :record="user" />

<!-- Em um card -->
<ActionGroup :actions="cardActions" :record="item" />

<!-- Bulk actions -->
<TableActions :actions="bulkActions" :records="selectedItems" mode="bulk" />

<!-- Custom modal -->
<ActionGroup :actions="[
  {
    type: 'modal',
    name: 'assign-role',
    label: 'Assign Role',
    modalComponent: AssignRoleModal,
  }
]" />
```

## ⚠️ Considerações

1. **Performance**: Actions condicionais não devem impactar performance
2. **Bundle Size**: Lazy load componentes de modal pesados
3. **Testes**: Adicionar testes para cada tipo de action
4. **Documentação**: Documentar cada prop e tipo de action
5. **Exemplos**: Criar página de exemplos/showcase

## 🚀 Próximos Passos

Após aprovação, implementar:
1. MVP: Modal de confirmação em Users
2. Refatorar TableActions
3. Criar sistema genérico
4. Documentar e criar exemplos
