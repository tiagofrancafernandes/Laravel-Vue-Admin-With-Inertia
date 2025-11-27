# Getting Started with AppMaker

AppMaker is a full-stack admin panel package for Laravel that integrates seamlessly with Inertia.js and Vue 3, allowing you to build complete CRUD interfaces with minimal code.

## Prerequisites

Before installing AppMaker, ensure you have:

- PHP 8.2 or higher
- Laravel 11.x
- Inertia.js 1.0+ or 2.0+
- Vue 3.4+
- Ziggy 2.0+
- Spatie Laravel Permission 6.0+

## Installation

### Step 1: Install the Package

If you're using AppMaker as a local package (path repository):

```bash
# Already configured in your composer.json
composer install
```

For production installations:

```bash
composer require appmaker/appmaker
```

### Step 2: Publish Configuration (Optional)

```bash
# Publish the configuration file
php artisan vendor:publish --tag=appmaker-config

# Publish Vue components (if you want to customize them)
php artisan vendor:publish --tag=appmaker-components
```

### Step 3: Configure Permissions

Make sure you have Spatie Permission installed and configured:

# Run migrations if not already done
```bash
php artisan migrate
```

# Create roles
```sh
php artisan tinker
```
and run
```psy
>>> \Spatie\Permission\Models\Role::create(['name' => 'admin']);
>>> \Spatie\Permission\Models\Role::create(['name' => 'user']);
```

## Quick Start Example

Let's create a complete CRUD for a `Post` model.

### Step 1: Create the Model and Migration

```bash
php artisan make:model Post -m
```

Update the migration:

```php
// database/migrations/xxxx_create_posts_table.php
public function up(): void
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('content');
        $table->string('status')->default('draft');
        $table->boolean('is_featured')->default(false);
        $table->foreignId('author_id')->constrained('users');
        $table->timestamps();
    });
}
```

Run the migration:

```bash
php artisan migrate
```

### Step 2: Update the Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'status',
        'is_featured',
        'author_id',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
```

### Step 3: Create the Resource

Create a new file at `app/AppMaker/Resources/PostResource.php`:

```php
<?php

namespace App\AppMaker\Resources;

use App\Models\Post;
use App\Models\User;
use AppMaker\Actions\Action;
use AppMaker\Forms\Components\Checkbox;
use AppMaker\Forms\Components\Select;
use AppMaker\Forms\Components\Textarea;
use AppMaker\Forms\Components\TextInput;
use AppMaker\Forms\Form;
use AppMaker\InfoLists\Entries\IconEntry;
use AppMaker\InfoLists\Entries\TextEntry;
use AppMaker\InfoLists\InfoList;
use AppMaker\Resources\ResourceBase;
use AppMaker\Tables\Columns\BadgeColumn;
use AppMaker\Tables\Columns\IconColumn;
use AppMaker\Tables\Columns\TextColumn;
use AppMaker\Tables\Filters\SelectFilter;
use AppMaker\Tables\Table;

class PostResource extends ResourceBase
{
    protected ?string $model = Post::class;
    protected ?string $uri = 'posts';
    protected ?string $resourceId = 'post';

    public function table(): Table
    {
        return Table::make()
            ->heading('Posts')
            ->striped(true)
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('author.name')
                    ->label('Author')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->colors([
                        'draft' => 'gray',
                        'reviewing' => 'yellow',
                        'published' => 'green',
                    ])
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->formatStateUsing(fn ($state) => $state->format('M d, Y'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'reviewing' => 'Reviewing',
                        'published' => 'Published',
                    ]),
            ])
            ->recordActions([
                Action::make('publish')
                    ->label('Publish')
                    ->icon('check')
                    ->color('green')
                    ->action(function (Post $record) {
                        $record->update(['status' => 'published']);
                    })
                    ->visible(fn (Post $record) => $record->status !== 'published')
                    ->requiresConfirmation(
                        true,
                        'Publish Post?',
                        'This will make the post visible to everyone.'
                    ),

                Action::make('feature')
                    ->label('Feature')
                    ->icon('star')
                    ->color('yellow')
                    ->action(function (Post $record) {
                        $record->update(['is_featured' => true]);
                    })
                    ->visible(fn (Post $record) => !$record->is_featured),
            ]);
    }

    public function form(): Form
    {
        return Form::make()
            ->heading('Post Details')
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Enter post title')
                    ->columnSpan(2),

                Textarea::make('content')
                    ->required()
                    ->rows(10)
                    ->helperText('Write your post content here')
                    ->columnSpan(2),

                Select::make('status')
                    ->required()
                    ->options([
                        'draft' => 'Draft',
                        'reviewing' => 'Reviewing',
                        'published' => 'Published',
                    ])
                    ->default('draft'),

                Select::make('author_id')
                    ->label('Author')
                    ->required()
                    ->options(fn () => User::pluck('name', 'id')->toArray())
                    ->searchable(),

                Checkbox::make('is_featured')
                    ->label('Feature this post')
                    ->helperText('Featured posts appear on homepage')
                    ->columnSpan(2),
            ])
            ->columns(2);
    }

    public function infoList(): InfoList
    {
        return InfoList::make()
            ->schema([
                TextEntry::make('title')->label('Title'),
                TextEntry::make('author.name')->label('Author'),
                TextEntry::make('status')
                    ->badge()
                    ->colors([
                        'draft' => 'gray',
                        'reviewing' => 'yellow',
                        'published' => 'green',
                    ]),
                IconEntry::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label('Created')
                    ->dateTime(),
                TextEntry::make('content')->label('Content')->columnSpan(2),
            ])
            ->columns(2);
    }
}
```

### Step 4: Register the Resource

Update `config/appmaker.php`:

```php
return [
    'resources' => [
        'posts' => \App\AppMaker\Resources\PostResource::class,
    ],

    // ... other config
];
```

### Step 5: Add Routes

In `routes/web.php`:

```php
use App\Http\Controllers\AppMaker\Http\Controllers\ResourceController;

Route::middleware(['auth'])->group(function () {
    Route::resource('posts', ResourceController::class)->parameters([
        'posts' => 'record'
    ]);
});
```

### Step 6: Create Permissions

```php
use App\Models\Permission;

Permission::create(['name' => 'view-posts']);
Permission::create(['name' => 'create-posts']);
Permission::create(['name' => 'update-posts']);
Permission::create(['name' => 'delete-posts']);

// Assign to admin role
$admin = Role::findByName('admin');
$admin->givePermissionTo([
    'view-posts',
    'create-posts',
    'update-posts',
    'delete-posts',
]);
```

### Step 7: Add Navigation Link

In `resources/js/Layouts/AuthenticatedLayout.vue`:

```vue
<NavLink :href="route('posts.index')" :active="route().current('posts.*')">
    Posts
</NavLink>
```

## That's It!

You now have a fully functional CRUD with:

- ✅ List page with search, filters, sorting, pagination
- ✅ Create/Edit forms with validation
- ✅ Show page with formatted data display
- ✅ Delete functionality
- ✅ Custom actions (Publish, Feature)
- ✅ Authorization checks
- ✅ Dark mode support
- ✅ Responsive design

All with ~100 lines of PHP code!

## Next Steps

- [Learn about Tables](./02-tables.md)
- [Explore Forms](./03-forms.md)
- [Customize Actions](./04-actions.md)
- [Work with InfoLists](./05-infolists.md)
- [Advanced Customization](./06-advanced.md)
