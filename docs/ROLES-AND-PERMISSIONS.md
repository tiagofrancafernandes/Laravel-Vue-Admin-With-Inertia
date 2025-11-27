# Roles and Permissions System

This boilerplate uses **Spatie Laravel Permission** package for managing user roles and permissions.

## 📋 Overview

The system implements a flexible role-based access control (RBAC) with 4 predefined roles and granular permissions.

## 🎭 Roles

### 1. SuperAdmin
**Description:** Has complete access to all system features and permissions.

**Key Characteristics:**
- Full system access
- Can manage all users, roles, and permissions
- Can view system logs and audit trails
- Cannot be restricted

**Use Case:** System owner, technical administrator

---

### 2. Admin
**Description:** Has most administrative permissions except critical system operations.

**Permissions:**
- ✅ View, create, update, delete users
- ✅ View, create, update, delete, restore products
- ✅ View and update settings
- ✅ View, export, and access financial reports
- ✅ View audit trail
- ✅ View roles
- ❌ Permanently delete users
- ❌ Manage system settings
- ❌ Delete system logs
- ❌ Create/modify roles

**Use Case:** Business administrator, operations manager

---

### 3. Manager
**Description:** Can manage users (except admins), products, and view reports.

**Permissions:**
- ✅ View, create, update users (non-admin users)
- ✅ View, create, update, delete products
- ✅ View and export reports
- ✅ View settings
- ❌ Delete users
- ❌ Update settings
- ❌ View financial reports
- ❌ View audit trail

**Use Case:** Team manager, department head

---

### 4. Staff
**Description:** Can view and manage products, view basic dashboard and reports.

**Permissions:**
- ✅ View dashboard
- ✅ View, create, update products
- ✅ View reports (basic)
- ❌ Manage users
- ❌ Delete products
- ❌ Export reports
- ❌ View settings

**Use Case:** Regular employee, data entry

---

## 🔐 Permissions List

### Dashboard
- `dashboard.view` - View dashboard

### Users Management
- `users.view` - View users
- `users.create` - Create users
- `users.update` - Update users
- `users.delete` - Delete users (soft delete)
- `users.restore` - Restore deleted users
- `users.force-delete` - Permanently delete users

### Products Management
- `products.view` - View products
- `products.create` - Create products
- `products.update` - Update products
- `products.delete` - Delete products
- `products.restore` - Restore deleted products

### Settings
- `settings.view` - View settings
- `settings.update` - Update settings
- `settings.manage-system` - Manage system settings

### Reports
- `reports.view` - View reports
- `reports.export` - Export reports
- `reports.financial` - View financial reports

### Logs & Audit
- `logs.view` - View system logs
- `logs.delete` - Delete system logs
- `audit.view` - View audit trail

### Roles & Permissions
- `roles.view` - View roles
- `roles.create` - Create roles
- `roles.update` - Update roles
- `roles.delete` - Delete roles
- `permissions.assign` - Assign permissions

---

## 🚀 Installation & Setup

### 1. Run Migrations

The Spatie Permission package requires migrations:

```bash
php artisan migrate
```

This will create the necessary tables:
- `roles`
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`

### 2. Seed Roles and Permissions

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

This creates:
- 4 roles (super-admin, admin, manager, staff)
- All permissions listed above
- Role-permission assignments

### 3. Seed Admin Users

```bash
php artisan db:seed --class=AdminUserSeeder
```

This creates 4 test users:

| Name | Email | Password | Role |
|------|-------|----------|------|
| Super Admin | superadmin@mail.com | power@123 | super-admin |
| Admin User | admin@mail.com | power@123 | admin |
| Manager User | manager@mail.com | power@123 | manager |
| Staff User | staff@mail.com | power@123 | staff |

**Or run all seeders at once:**

```bash
php artisan db:seed
```

---

## 💻 Usage in Code

### Backend (PHP/Laravel)

#### Check if user has role

```php
// Using Spatie methods
if ($user->hasRole('super-admin')) {
    // User is super admin
}

// Using helper methods (in User model)
if ($user->isSuperAdmin()) {
    // User is super admin
}

if ($user->isManager()) {
    // User is manager
}
```

#### Check if user has permission

```php
// Direct permission check
if ($user->can('users.create')) {
    // User can create users
}

// Using helper methods
if ($user->canManageUsers()) {
    // User can manage users
}
```

#### Assign role to user

```php
$user->assignRole('manager');

// Or multiple roles
$user->assignRole(['manager', 'staff']);

// Sync roles (removes old, adds new)
$user->syncRoles(['admin']);
```

#### Give permission to user

```php
$user->givePermissionTo('products.create');

// Multiple permissions
$user->givePermissionTo(['products.create', 'products.update']);
```

#### Using in Controllers

```php
// Using authorize() method
public function store(Request $request)
{
    $this->authorize('create', Product::class); // Uses ProductPolicy

    // or direct permission check
    if (!auth()->user()->can('products.create')) {
        abort(403);
    }

    // ...
}
```

#### Using Middleware

```php
// In routes/web.php
Route::middleware(['auth', 'role:super-admin'])->group(function () {
    Route::resource('users', UserController::class);
});

Route::middleware(['auth', 'permission:products.create'])->group(function () {
    Route::post('products', [ProductController::class, 'store']);
});
```

---

### Frontend (Vue/Inertia)

#### Share permissions with frontend

In `app/Http/Middleware/HandleInertiaRequests.php`:

```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user() ? [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->roles()->latest()->first(),
                'roles' => $request->user()->getRoleNames(), // Spatie roles
                'permissions' => $request->user()->getAllPermissions()->pluck('name'), // All permissions
            ] : null,
        ],
    ];
}
```

#### Use in Vue components

```vue
<template>
    <div>
        <!-- Show button only if user has permission -->
        <button v-if="can('users.create')">
            Create User
        </button>

        <!-- Show section only for SuperAdmin -->
        <div v-if="hasRole('super-admin')">
            System Settings
        </div>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const hasRole = (role) => {
    return user.value?.roles?.includes(role) || false;
};

const can = (permission) => {
    return user.value?.permissions?.includes(permission) || false;
};
</script>
```

#### Using composable (recommended)

Update `resources/js/Composables/useAuth.js`:

```javascript
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useAuth() {
    const page = usePage();
    const user = computed(() => page.props.auth?.user || null);

    const hasRole = (role) => {
        return user.value?.roles?.includes(role) || false;
    };

    const can = (permission) => {
        return user.value?.permissions?.includes(permission) || false;
    };

    const isSuperAdmin = computed(() => hasRole('super-admin'));
    const isAdmin = computed(() => hasRole('admin'));
    const isManager = computed(() => hasRole('manager'));
    const isStaff = computed(() => hasRole('staff'));

    return {
        user,
        hasRole,
        can,
        isSuperAdmin,
        isAdmin,
        isManager,
        isStaff,
    };
}
```

Then in components:

```vue
<script setup>
import { useAuth } from '@/Composables/useAuth';

const { can, isSuperAdmin, isManager } = useAuth();
</script>

<template>
    <button v-if="can('users.create')">Create User</button>
    <div v-if="isSuperAdmin">SuperAdmin Section</div>
</template>
```

---

## 🎯 Best Practices

### 1. Use Permissions, Not Roles in Controllers

❌ Bad:
```php
if ($user->hasRole('admin')) {
    // Allow action
}
```

✅ Good:
```php
if ($user->can('users.create')) {
    // Allow action
}
```

**Why?** Permissions are more granular and flexible. Roles can change, permissions are stable.

---

### 2. Use Policies for Resource Authorization

Create policies for each model:

```bash
php artisan make:policy ProductPolicy --model=Product
```

```php
class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('products.view');
    }

    public function create(User $user): bool
    {
        return $user->can('products.create');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('products.update');
    }
}
```

---

### 3. Cache Permissions

Spatie Permission caches permissions automatically. To clear cache:

```bash
php artisan permission:cache-reset
```

Or in code:

```php
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
```

---

### 4. Create Custom Permissions

```php
use App\Models\Permission;

Permission::create(['name' => 'custom.permission']);
```

Then assign to role:

```php
$role = Role::findByName('manager');
$role->givePermissionTo('custom.permission');
```

---

## 📊 Permission Matrix

| Permission | SuperAdmin | Admin | Manager | Staff |
|------------|------------|-------|---------|-------|
| dashboard.view | ✅ | ✅ | ✅ | ✅ |
| users.view | ✅ | ✅ | ✅ | ❌ |
| users.create | ✅ | ✅ | ✅ | ❌ |
| users.update | ✅ | ✅ | ✅ | ❌ |
| users.delete | ✅ | ✅ | ❌ | ❌ |
| users.restore | ✅ | ✅ | ❌ | ❌ |
| users.force-delete | ✅ | ❌ | ❌ | ❌ |
| products.view | ✅ | ✅ | ✅ | ✅ |
| products.create | ✅ | ✅ | ✅ | ✅ |
| products.update | ✅ | ✅ | ✅ | ✅ |
| products.delete | ✅ | ✅ | ✅ | ❌ |
| products.restore | ✅ | ✅ | ❌ | ❌ |
| settings.view | ✅ | ✅ | ✅ | ❌ |
| settings.update | ✅ | ✅ | ❌ | ❌ |
| settings.manage-system | ✅ | ❌ | ❌ | ❌ |
| reports.view | ✅ | ✅ | ✅ | ✅ |
| reports.export | ✅ | ✅ | ✅ | ❌ |
| reports.financial | ✅ | ✅ | ❌ | ❌ |
| logs.view | ✅ | ❌ | ❌ | ❌ |
| logs.delete | ✅ | ❌ | ❌ | ❌ |
| audit.view | ✅ | ✅ | ❌ | ❌ |
| roles.view | ✅ | ✅ | ❌ | ❌ |
| roles.create | ✅ | ❌ | ❌ | ❌ |
| roles.update | ✅ | ❌ | ❌ | ❌ |
| roles.delete | ✅ | ❌ | ❌ | ❌ |
| permissions.assign | ✅ | ❌ | ❌ | ❌ |

---

## 🔧 Troubleshooting

### Permission denied even though user has permission

**Solution:** Clear permission cache:
```bash
php artisan permission:cache-reset
```

### User not getting permissions after role assignment

**Solution:** Make sure to use `syncRoles()` or `assignRole()`, not direct database manipulation.

### Frontend not showing permissions

**Solution:** Check that permissions are being shared in `HandleInertiaRequests.php`.

---

## 📚 References

- [Spatie Laravel Permission Documentation](https://spatie.be/docs/laravel-permission)
- [Laravel Authorization Documentation](https://laravel.com/docs/authorization)
- [Laravel Policies](https://laravel.com/docs/authorization#creating-policies)

---

**Last Updated:** November 27, 2025
**Version:** 1.0
**Status:** ✅ Production Ready
