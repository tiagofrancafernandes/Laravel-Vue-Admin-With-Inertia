# Laravel + Vue 3 + Inertia.js Boilerplate - Implementation Checklist

This checklist guides you through using this boilerplate to build your next application. Follow each step to set up your project.

## 1. Project Setup

### Core Setup (5 minutes)
- [ ] Clone the repository or create new project from this boilerplate
- [ ] Copy `.env.example` to `.env`
- [ ] Run `composer install && npm install`
- [ ] Run `php artisan key:generate`
- [ ] Update `.env` with your database configuration

### Database Setup (2 minutes)
- [ ] Configure your database in `.env` (SQLite, MySQL, PostgreSQL supported)
- [ ] Run `php artisan migrate`
- [ ] (Optional) Run `php artisan tinker` to create a test admin user
- [ ] Verify Users table has `id`, `name`, `email`, `password`, `role`, `email_verified_at`, `created_at`, `updated_at`

### Development Server (1 minute)
- [ ] Run `composer run dev` to start the development server
- [ ] Open browser to `http://localhost:8000`
- [ ] Verify you can access the application and see the welcome page

---

## 2. Authentication & User Management

### Understanding the Current Setup
- [ ] Review `app/Models/User.php` - understand the generic `role` field (admin/user)
- [ ] Check `app/Policies/UserPolicy.php` - understand authorization checks
- [ ] Review `routes/auth.php` - understand authentication routes
- [ ] Check `resources/js/Pages/Auth/` - understand auth flow in Vue

### Users CRUD Example (Already Implemented!)
- [ ] View `app/Http/Controllers/UserController.php` - full CRUD example
- [ ] Review `resources/js/Pages/Resources/Users/` folder structure
- [ ] Open `/users` in browser to see the users list page
- [ ] Create, read, update, delete users to understand the CRUD pattern
- [ ] **Use this Users CRUD as a template for your own resources**

---

## 3. Creating Your First Resource

Choose what your application manages (Products, Posts, Articles, Tasks, etc.) and follow this pattern:

### Step 1: Create Model & Migration (5 min)
- [ ] Run `php artisan make:model YourResource -m`
- [ ] Edit `database/migrations/` file to define your table columns
- [ ] Run `php artisan migrate`
- [ ] Update `app/Models/YourResource.php` with relationships and fillable attributes

### Step 2: Create Controller (10 min)
- [ ] Run `php artisan make:controller YourResourceController`
- [ ] Copy the structure from `UserController.php` as a template
- [ ] Implement index (with pagination), create, store, show, edit, update, destroy methods
- [ ] Add search/filtering logic in index method
- [ ] Reference: `UserController::index()` for pagination example

### Step 3: Create Form Requests (5 min)
- [ ] Run `php artisan make:request StoreYourResourceRequest`
- [ ] Run `php artisan make:request UpdateYourResourceRequest`
- [ ] Define validation rules for create and update
- [ ] Reference: `StoreUserRequest` and `UpdateUserRequest` as templates

### Step 4: Create Policy (3 min)
- [ ] Run `php artisan make:policy YourResourcePolicy --model=YourResource`
- [ ] Implement viewAny, view, create, update, delete methods
- [ ] Define who can perform each action (admin-only, owner-only, etc.)
- [ ] Reference: `UserPolicy.php` as a template

### Step 5: Add Routes (2 min)
- [ ] Open `routes/web.php`
- [ ] Add `Route::resource('your-resources', YourResourceController::class);` inside the auth middleware group
- [ ] Test routes: `php artisan route:list | grep your-resources`

### Step 6: Create Vue Pages (30 min)
- [ ] Create folder `resources/js/Pages/Resources/YourResources/`
- [ ] Create `Index.vue` - listing with pagination and filters
- [ ] Create `Create.vue` - form to create new resource
- [ ] Create `Edit.vue` - form to edit existing resource
- [ ] Create `Show.vue` - detailed view of resource
- [ ] (Optional) Create `YourResourceForm.vue` - reusable form component
- [ ] Reference: `resources/js/Pages/Resources/Users/` folder for complete example

### Step 7: Create Factory (5 min)
- [ ] Run `php artisan make:factory YourResourceFactory`
- [ ] Define fake data generation for testing
- [ ] Reference: `database/factories/UserFactory.php` as a template

### Step 8: Create Tests (15 min)
- [ ] Run `php artisan make:test Pages/YourResourcesPagesTest`
- [ ] Add tests for: index page access, create page, store validation, show, edit, update, delete
- [ ] Test authorization (who can access what)
- [ ] Reference: Look at existing tests patterns in `tests/Feature/Pages/`

### Step 9: Test Your Resource
- [ ] Run `php artisan test` to run tests
- [ ] Manually test CRUD operations in browser
- [ ] Test permissions (create admin and regular user accounts)
- [ ] Verify pagination works with multiple records

---

## 4. Advanced Features

### Adding Soft Deletes (when data should be recoverable)
- [ ] Add `use SoftDeletes;` to your Model
- [ ] Add `$table->softDeletes();` to migration
- [ ] Update Policy to handle restore and force delete
- [ ] Reference: See migrations with `softDeletes()` for examples

### Adding Activity Logging
- [ ] (Optional) Integrate `spatie/laravel-activitylog` for audit trails
- [ ] Reference: BOILERPLATE_SETUP.md for more integration details

### Adding Real-time Features
- [ ] (Optional) Set up WebSockets or Server-Sent Events (SSE)
- [ ] Consider: Broadcasting updates to multiple users

### Adding Search/Filtering
- [ ] (Optional) Add advanced search with Scouts or manual queries
- [ ] Reference: `UserController::index()` shows basic filtering pattern

### Adding File Uploads
- [ ] Use Laravel's built-in file storage
- [ ] Store files in `storage/app/public` or S3
- [ ] Create disk configuration in `config/filesystems.php`

---

## 5. Styling & UI

### Dark Mode
- [ ] Dark mode is already configured in Tailwind
- [ ] Check `useDarkMode()` composable if needed
- [ ] Use dark: prefixes in Tailwind classes (already done in examples)

### Component Library
- [ ] Generic UI components available in `resources/js/Components/`
  - [ ] `Common/Modal.vue`, `Button.vue`, `Input.vue`, `Badge.vue`
  - [ ] `Forms/Input.vue`, `Select.vue`, `Button.vue`
  - [ ] `UI/Card.vue`, `Table.vue`, `Loading.vue`, `Alert.vue`
- [ ] Use these components in your own pages for consistency
- [ ] Customize component styles via Tailwind classes

### Tailwind CSS
- [ ] Tailwind v3 is configured with dark mode support
- [ ] Extend colors in `tailwind.config.js` if needed
- [ ] Use responsive classes: `md:`, `lg:`, `xl:` prefixes
- [ ] Reference: All Users CRUD pages show best practices

---

## 6. Customization

### Update Dashboard
- [ ] Edit `app/Http/Controllers/DashboardController.php` to show your app's data
- [ ] Edit `resources/js/Pages/Dashboard.vue` to display custom stats
- [ ] Current dashboard shows generic user statistics as a template

### Update Navigation
- [ ] Edit `resources/js/Layouts/AuthenticatedLayout.vue` to add your resources
- [ ] Add navigation items for your CRUD pages
- [ ] Update the sidebar/header menu to match your app

### Customize Layouts
- [ ] `GuestLayout.vue` - for auth pages (login, register)
- [ ] `AuthenticatedLayout.vue` - for app pages
- [ ] Create custom layouts if needed for specific sections
- [ ] Reference: Existing layouts in `resources/js/Layouts/`

---

## 7. Deployment Preparation

### Code Quality
- [ ] Run `./vendor/bin/pint` to format PHP code (PSR-12)
- [ ] Run `npx prettier --write resources/js` to format Vue/JS code
- [ ] Run `php artisan test` to ensure all tests pass

### Configuration for Production
- [ ] Update `.env` with production database details
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Generate app key if needed: `php artisan key:generate`
- [ ] Run `php artisan config:cache` to cache configuration

### Database Migrations
- [ ] Review all migrations in `database/migrations/`
- [ ] Test migration rollback: `php artisan migrate:rollback`
- [ ] Test fresh migration: `php artisan migrate:fresh`

### Build for Production
- [ ] Run `npm run build` to build production assets
- [ ] Verify `public/build/` contains compiled files
- [ ] Test on a staging environment first

---

## 8. Useful Commands Reference

### Development
```bash
# Start dev server with hot reload
composer run dev

# Run tests
php artisan test
php artisan test --filter=UserTest

# Format code
./vendor/bin/pint
npx prettier --write resources/js

# Create scaffolding
php artisan make:model YourModel -mcfr  # Model, migration, controller, factory, request
php artisan tinker                       # Interactive shell
```

### Database
```bash
# Migrations
php artisan migrate                      # Run all pending migrations
php artisan migrate:fresh                # Reset and re-run migrations (⚠️ deletes data!)
php artisan migrate:rollback             # Undo last migration batch

# Seeds
php artisan db:seed                      # Run seeders
php artisan tinker                       # Manually create data
```

### Routes
```bash
# List all routes
php artisan route:list

# List specific resource routes
php artisan route:list | grep products
```

---

## 9. Troubleshooting

### Common Issues

**Issue: Migrations say "table doesn't exist"**
- Solution: Run `php artisan migrate` to create tables

**Issue: "Class not found" errors**
- Solution: Run `composer dumpautoload` to regenerate autoloader

**Issue: Vue components not loading**
- Solution: Make sure you imported them in the page file
- Check browser console for specific errors

**Issue: Dark mode not working**
- Solution: Make sure `<html class="dark">` is set (handled automatically)

**Issue: Pagination links incorrect**
- Solution: Verify you're using `appends()` method on query builder to preserve filters

**Issue: Permission denied when uploading files**
- Solution: Check Laravel storage permissions: `chmod -R 775 storage/`

---

## 10. Next Steps After Setup

1. **Customize for your use case**
   - [ ] Add your own resources (Products, Posts, etc.)
   - [ ] Implement business logic in services
   - [ ] Add custom validation rules

2. **Enhance user experience**
   - [ ] Add loading states and transitions
   - [ ] Implement toast/notification system
   - [ ] Add modal dialogs for confirmations

3. **Improve performance**
   - [ ] Add database indexing
   - [ ] Implement caching strategies
   - [ ] Optimize queries with eager loading

4. **Add features**
   - [ ] User roles and permissions system
   - [ ] File uploads and management
   - [ ] Real-time notifications
   - [ ] API endpoints for mobile apps

5. **Testing**
   - [ ] Write comprehensive tests for all features
   - [ ] Set up continuous integration (CI/CD)
   - [ ] Load testing for production readiness

6. **Documentation**
   - [ ] Document your API endpoints
   - [ ] Document your custom business logic
   - [ ] Create setup guide for new developers

---

## Final Checklist

- [ ] All tests passing (`php artisan test`)
- [ ] Code formatted (`./vendor/bin/pint && npx prettier --write resources/js`)
- [ ] Migrations working (`php artisan migrate:fresh`)
- [ ] Dashboard displaying correctly
- [ ] Users CRUD fully functional
- [ ] Authentication system verified
- [ ] Production build tested (`npm run build`)
- [ ] Documentation updated
- [ ] Ready for initial deployment! 🚀

---

**Questions?** Check:
- `BOILERPLATE_SETUP.md` - Detailed implementation guide
- `CLAUDE.md` - Project-specific guidelines
- `app/Http/Controllers/UserController.php` - Example CRUD controller
- `resources/js/Pages/Resources/Users/` - Example Vue pages
- Official docs: [Laravel](https://laravel.com/docs), [Vue 3](https://vuejs.org/), [Inertia.js](https://inertiajs.com/)
