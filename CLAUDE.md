# CLAUDE.md - Laravel + Vue 3 + Inertia.js Admin Boilerplate

This file provides guidance to Claude Code when working with this boilerplate.

## 📋 Project Overview

This is a **generic Laravel 11 + Vue 3 + Inertia.js admin boilerplate** designed to be a starting point for building admin panels and web applications. It provides:

- **User authentication** with email verification
- **User management CRUD** as a complete example
- **Generic dashboard** with system statistics
- **Role-based authorization** (admin/user)
- **Responsive UI** with dark mode support using Tailwind CSS v3
- **Production-ready patterns** following PSR-12 and Vue 3 best practices

**This is NOT a specific application** - it's a foundation to build upon. Use the Users CRUD as a template for your own resources.

## 🚀 Quick Start

```bash
# Setup
composer install && npm install
cp .env.example .env
php artisan key:generate
php artisan migrate

# Development
composer run dev

# Testing
composer run test

# Code formatting
./vendor/bin/pint
npx prettier --write resources/js
```

## 📁 Key Project Structure

```
app/Http/
├── Controllers/
│   ├── UserController.php          ← CRUD example to copy
│   ├── DashboardController.php     ← Generic dashboard
│   └── ProfileController.php       ← User profile management
├── Requests/
│   ├── StoreUserRequest.php        ← Form validation example
│   └── UpdateUserRequest.php
└── Middleware/
    └── HandleInertiaRequests.php   ← Inertia setup

app/Models/
└── User.php                        ← Generic user with 'role' field

app/Policies/
└── UserPolicy.php                  ← Authorization example

resources/js/Pages/
├── Resources/Users/                ← CRUD UI example
│   ├── Index.vue
│   ├── Create.vue
│   ├── Edit.vue
│   ├── Show.vue
│   └── UserForm.vue
├── Dashboard.vue                   ← Generic dashboard template
└── Auth/                           ← Authentication pages

routes/web.php                      ← Resource routes go here
```

## 🎯 How to Use This Boilerplate

### 1. Understanding the Example
- **Review** `UserController` - complete CRUD controller example
- **Review** `resources/js/Pages/Resources/Users/` - Vue page examples
- **Review** `StoreUserRequest` & `UpdateUserRequest` - validation examples
- **Review** `UserPolicy` - authorization pattern example

### 2. Creating Your First Resource
1. Follow the step-by-step guide in `CHECKLIST.md`
2. Copy the structure from `UserController` for your own resource
3. Create migrations, models, form requests, policies, routes, and Vue pages
4. Reference existing Users CRUD for any patterns you need

### 3. Customize
- Update `DashboardController` to show your app's data
- Modify navigation in `AuthenticatedLayout.vue`
- Add your resource routes to `routes/web.php`

## 🏛️ Architecture Patterns

### Controllers
- Use `authorizeResource()` in constructor
- Return `Inertia::render()` for pages
- Support both HTML and JSON responses
- Implement proper pagination with `paginate(15)`

```php
public function __construct() {
    $this->authorizeResource(YourModel::class, 'your_model');
}
```

### Models
- Use `SoftDeletes` for recoverable data
- Define `$fillable` array for mass assignment
- Type-hint relationships
- Use Factories for testing

### Form Requests
- Validate all input before controller processes it
- Use `$this->user()->isAdmin()` for authorization
- Return meaningful error messages

### Policies
- Define who can perform each action
- Check roles/ownership in methods
- Return boolean to allow/deny

### Vue Pages
- Use Composition API with `<script setup>`
- Use object syntax for conditional classes
- Create reusable form components
- Support dark mode with `dark:` prefixes

## 🧪 Testing

```bash
# All tests
composer run test

# Specific file
php artisan test tests/Feature/Pages/UsersPagesTest.php

# Specific test
php artisan test tests/Feature/Pages/UsersPagesTest.php --filter=testAdminCanAccessUsersIndex
```

**Pattern for page access tests:**
```php
public function testAdminCanAccessUsersIndexPage(): void {
    $user = User::factory()->admin()->create();
    $response = $this->actingAs($user)->get(route('users.index'));
    $response->assertStatus(200);
}
```

## 💾 Database

- Migrations in `database/migrations/`
- Factories in `database/factories/`
- Seeds in `database/seeders/`
- User table has `role` field: `admin` or `user`

## 🎨 Styling

- **Framework**: Tailwind CSS v3
- **Dark mode**: Enabled with `dark:` prefixes
- **Responsive**: Use `md:`, `lg:`, `xl:` breakpoints
- **Components**: Reusable components in `resources/js/Components/`

## 🔐 Security

- All routes use `auth` middleware by default
- Policies enforce authorization at controller level
- Form Requests validate all input
- CSRF protection enabled by default
- Soft deletes prevent permanent data loss

## 📚 Important Files to Know

| File | Purpose |
|------|---------|
| `BOILERPLATE_SETUP.md` | Detailed implementation guide with examples |
| `CHECKLIST.md` | Step-by-step checklist for building your app |
| `UserController.php` | **CRUD example** - copy this structure |
| `UserPolicy.php` | **Authorization example** - copy this pattern |
| `StoreUserRequest.php` | **Validation example** - use for own resources |
| `resources/js/Pages/Resources/Users/` | **Vue pages example** - reference for your UI |

## 🛠️ Common Tasks

### Add a new resource (Product, Post, etc.)
1. Create model: `php artisan make:model Product -m`
2. Update migration with columns
3. Create controller: `php artisan make:controller ProductController`
4. Copy `UserController` structure as template
5. Create form requests and policy
6. Add routes in `web.php`
7. Create Vue pages in `resources/js/Pages/Resources/Products/`
8. Create tests in `tests/Feature/Pages/`

### Add authentication check
```php
// In controller
$this->authorize('view', $model);

// In policy
public function view(User $user, Product $product): bool {
    return $user->isAdmin();
}
```

### Add pagination with filters
```php
$query = Product::query();
if ($request->filled('search')) {
    $query->where('name', 'like', "%{$request->input('search')}%");
}
return $query->paginate(15)->appends($request->query());
```

## 🚨 Troubleshooting

**Issue**: `Table doesn't exist` error
- **Fix**: Run `php artisan migrate`

**Issue**: `Class not found` in controller
- **Fix**: Run `composer dumpautoload`

**Issue**: Vue component not showing
- **Fix**: Verify component is imported in the page file

**Issue**: Pagination links broken
- **Fix**: Use `appends()` to preserve query parameters

## 📝 Development Preferences (from CLAUDE.md)

- **PHP**: PSR-12 standards via Pint
- **Vue**: Composition API with `<script setup>`
- **Classes**: Use object syntax for conditional bindings
- **Indentation**: 4 spaces (no tabs)
- **Commits**: Meaningful messages describing the "why"

## 🎓 Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Vue 3 Guide](https://vuejs.org/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Tailwind CSS](https://tailwindcss.com/)

## ✅ Boilerplate Checklist

- [x] User authentication (login, register, password reset)
- [x] User management CRUD (complete example)
- [x] Role-based authorization
- [x] Generic dashboard
- [x] Responsive UI with Tailwind CSS
- [x] Dark mode support
- [x] PSR-12 code standards
- [x] Vue 3 best practices
- [x] Complete documentation
- [x] Testing patterns
- [x] Ready to extend with custom resources

---

**Ready to start?** Follow the `CHECKLIST.md` step-by-step guide to build your application!
- Quando os testes falharem no por exemplo no momento do commit e precisar re executar um teste para ver detalhes do erro ou testar a ressolução, execute com '--filter' e --stop-on-failure/--stop-on-error, exemplo:
```sh
php artisan test --stop-on-failure --stop-on-error --cache-result --order-by=defects --filter=UsersPagesTest
```