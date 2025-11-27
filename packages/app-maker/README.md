# AppMaker

Full-stack admin panel components for Laravel + Inertia.js + Vue 3.

## Features

- **Declarative PHP API** - Define tables, forms, and pages entirely in PHP
- **Zero Frontend Code** - Vue components are generated automatically
- **Built-in Authorization** - Integrates with Spatie Laravel Permission
- **Type-Safe Routes** - Uses Ziggy for type-safe routing in Vue
- **SPA Experience** - Powered by Inertia.js for seamless navigation
- **80% Less Code** - Compared to traditional CRUD implementations

## Installation

```bash
# Package is auto-discovered
composer require appmaker/appmaker

# Publish config (optional)
php artisan vendor:publish --tag=appmaker-config

# Publish Vue components (optional)
php artisan vendor:publish --tag=appmaker-components
```

## Quick Start

### 1. Create a Resource

```php
<?php

namespace App\AppMaker\Resources;

use AppMaker\Resources\ResourceBase;
use AppMaker\Tables\Table;
use AppMaker\Tables\Columns\TextColumn;
use AppMaker\Forms\Form;
use AppMaker\Forms\Components\TextInput;

class PostResource extends ResourceBase
{
    protected ?string $model = \App\Models\Post::class;
    protected ?string $uri = 'posts';

    public function table(): Table
    {
        return Table::make()
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('author.name'),
            ]);
    }

    public function form(): Form
    {
        return Form::make()
            ->schema([
                TextInput::make('title')->required(),
            ]);
    }
}
```

### 2. Register the Resource

```php
// config/appmaker.php
return [
    'resources' => [
        'posts' => \App\AppMaker\Resources\PostResource::class,
    ],
];
```

### 3. Add Routes

```php
// routes/web.php
use AppMaker\Http\Controllers\ResourceController;

Route::resource('posts', ResourceController::class);
```

### 4. Setup Permissions

```php
// Create permissions
Permission::create(['name' => 'view-posts']);
Permission::create(['name' => 'create-posts']);
Permission::create(['name' => 'update-posts']);
Permission::create(['name' => 'delete-posts']);
```

That's it! You now have a fully functional CRUD with:
- List page with search, filters, sorting, pagination
- Create/Edit forms with validation
- Show page
- Delete functionality
- Authorization checks

## Documentation

See `/docs` directory for complete documentation.

## License

MIT
