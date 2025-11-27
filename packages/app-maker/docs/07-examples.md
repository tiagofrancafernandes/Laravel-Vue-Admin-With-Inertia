# Practical Examples

Real-world examples of complete resource implementations.

## Example 1: Blog System

Complete blog post management with categories, tags, and publishing workflow.

```php
<?php

namespace App\AppMaker\Resources;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use AppMaker\Actions\Action;
use AppMaker\Actions\BulkAction;
use AppMaker\Actions\BulkActionGroup;
use AppMaker\Actions\DeleteBulkAction;
use AppMaker\Forms\Components\Checkbox;
use AppMaker\Forms\Components\DatePicker;
use AppMaker\Forms\Components\FileUpload;
use AppMaker\Forms\Components\Select;
use AppMaker\Forms\Components\Textarea;
use AppMaker\Forms\Components\TextInput;
use AppMaker\Forms\Form;
use AppMaker\InfoLists\Entries\IconEntry;
use AppMaker\InfoLists\Entries\ImageEntry;
use AppMaker\InfoLists\Entries\TextEntry;
use AppMaker\InfoLists\InfoList;
use AppMaker\Resources\ResourceBase;
use AppMaker\Tables\Columns\BadgeColumn;
use AppMaker\Tables\Columns\IconColumn;
use AppMaker\Tables\Columns\ImageColumn;
use AppMaker\Tables\Columns\TextColumn;
use AppMaker\Tables\Filters\Filter;
use AppMaker\Tables\Filters\SelectFilter;
use AppMaker\Tables\Table;

class PostResource extends ResourceBase
{
    protected ?string $model = Post::class;
    protected ?string $uri = 'posts';

    public function table(): Table
    {
        return Table::make()
            ->heading('Blog Posts')
            ->striped(true)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),

                ImageColumn::make('featured_image')
                    ->disk('public')
                    ->width(60)
                    ->height(40),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('author.name')
                    ->label('Author')
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Category'),

                BadgeColumn::make('status')
                    ->colors([
                        'draft' => 'gray',
                        'reviewing' => 'yellow',
                        'published' => 'green',
                        'archived' => 'red',
                    ])
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('views')
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->formatStateUsing(fn ($state) => $state?->format('M d, Y'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'reviewing' => 'Under Review',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),

                SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(fn () => Category::pluck('name', 'id')),

                Filter::make('featured')
                    ->label('Featured Only')
                    ->query(fn ($query) => $query->where('is_featured', true)),
            ])
            ->recordActions([
                Action::make('publish')
                    ->label('Publish')
                    ->icon('tabler:send')
                    ->color('green')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->requiresConfirmation(
                        true,
                        'Publish Post?',
                        'This will make the post visible to all users.'
                    )
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'published',
                            'published_at' => now(),
                        ]);
                    }),

                Action::make('feature')
                    ->icon('heroicons:star')
                    ->color('yellow')
                    ->visible(fn ($record) => !$record->is_featured)
                    ->action(fn ($record) => $record->update(['is_featured' => true])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->icon('tabler:send')
                        ->action(fn ($records) => $records->each->publish()),

                    BulkAction::make('archive')
                        ->icon('tabler:archive')
                        ->action(fn ($records) => $records->each->archive()),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function form(): Form
    {
        return Form::make()
            ->columns(2)
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),

                Textarea::make('excerpt')
                    ->maxLength(500)
                    ->rows(3)
                    ->helperText('Brief summary (max 500 characters)')
                    ->columnSpan(2),

                Textarea::make('content')
                    ->required()
                    ->rows(15)
                    ->columnSpan(2),

                FileUpload::make('featured_image')
                    ->image()
                    ->disk('public')
                    ->directory('posts/featured')
                    ->maxSize(2048)
                    ->columnSpan(2),

                Select::make('category_id')
                    ->required()
                    ->options(fn () => Category::pluck('name', 'id'))
                    ->searchable(),

                Select::make('tags')
                    ->multiple()
                    ->options(fn () => Tag::pluck('name', 'id'))
                    ->searchable(),

                Select::make('status')
                    ->required()
                    ->options([
                        'draft' => 'Draft',
                        'reviewing' => 'Reviewing',
                        'published' => 'Published',
                    ])
                    ->default('draft'),

                Select::make('author_id')
                    ->required()
                    ->options(fn () => User::pluck('name', 'id'))
                    ->default(fn () => auth()->id()),

                DatePicker::make('published_at')
                    ->withTime()
                    ->format('Y-m-d H:i'),

                Checkbox::make('is_featured')
                    ->label('Feature this post'),
            ]);
    }

    public function infoList(): InfoList
    {
        return InfoList::make()
            ->columns(2)
            ->schema([
                ImageEntry::make('featured_image')
                    ->width(600)
                    ->columnSpan(2),

                TextEntry::make('title')->columnSpan(2),
                TextEntry::make('author.name'),
                TextEntry::make('category.name'),

                TextEntry::make('status')->badge()
                    ->colors([
                        'draft' => 'gray',
                        'published' => 'green',
                    ]),

                IconEntry::make('is_featured')->boolean(),

                TextEntry::make('views')
                    ->formatStateUsing(fn ($state) => number_format($state)),

                TextEntry::make('published_at')->dateTime(),
                TextEntry::make('excerpt')->columnSpan(2),
                TextEntry::make('content')->columnSpan(2),
            ]);
    }
}
```

## Example 2: E-commerce Products

Product management with inventory, pricing, and categories.

```php
<?php

namespace App\AppMaker\Resources;

use App\Models\Category;
use App\Models\Product;
use AppMaker\Actions\Action;
use AppMaker\Forms\Components\Checkbox;
use AppMaker\Forms\Components\FileUpload;
use AppMaker\Forms\Components\Select;
use AppMaker\Forms\Components\Textarea;
use AppMaker\Forms\Components\TextInput;
use AppMaker\Forms\Form;
use AppMaker\Resources\ResourceBase;
use AppMaker\Tables\Columns\BadgeColumn;
use AppMaker\Tables\Columns\ImageColumn;
use AppMaker\Tables\Columns\TextColumn;
use AppMaker\Tables\Filters\Filter;
use AppMaker\Tables\Filters\SelectFilter;
use AppMaker\Tables\Table;

class ProductResource extends ResourceBase
{
    protected ?string $model = Product::class;
    protected ?string $uri = 'products';

    public function table(): Table
    {
        return Table::make()
            ->heading('Products')
            ->columns([
                ImageColumn::make('image')
                    ->disk('public')
                    ->rounded()
                    ->width(50)
                    ->height(50),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('category.name'),

                TextColumn::make('price')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state, 2, ',', '.'))
                    ->sortable(),

                TextColumn::make('stock')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->colors([
                        'active' => 'green',
                        'inactive' => 'gray',
                        'out_of_stock' => 'red',
                    ]),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'out_of_stock' => 'Out of Stock',
                    ]),

                SelectFilter::make('category_id')
                    ->options(fn () => Category::pluck('name', 'id')),

                Filter::make('low_stock')
                    ->label('Low Stock')
                    ->query(fn ($query) => $query->where('stock', '<', 10)),
            ])
            ->recordActions([
                Action::make('restock')
                    ->icon('tabler:package')
                    ->color('blue')
                    ->visible(fn ($record) => $record->stock < 10)
                    ->action(fn ($record) => $record->update(['stock' => 100])),
            ]);
    }

    public function form(): Form
    {
        return Form::make()
            ->columns(2)
            ->schema([
                TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->rules(['unique:products,sku']),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->rows(5)
                    ->columnSpan(2),

                TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->helperText('Price in BRL'),

                TextInput::make('stock')
                    ->numeric()
                    ->required()
                    ->default(0),

                Select::make('category_id')
                    ->required()
                    ->options(fn () => Category::pluck('name', 'id')),

                Select::make('status')
                    ->required()
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'out_of_stock' => 'Out of Stock',
                    ])
                    ->default('active'),

                FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('products')
                    ->columnSpan(2),

                Checkbox::make('is_featured')
                    ->label('Feature on homepage'),
            ]);
    }
}
```

## Example 3: User Management

Complete user management with roles and permissions.

```php
<?php

namespace App\AppMaker\Resources;

use App\Models\User;
use AppMaker\Actions\Action;
use AppMaker\Forms\Components\Checkbox;
use AppMaker\Forms\Components\Select;
use AppMaker\Forms\Components\TextInput;
use AppMaker\Forms\Form;
use AppMaker\Resources\ResourceBase;
use AppMaker\Tables\Columns\BadgeColumn;
use AppMaker\Tables\Columns\IconColumn;
use AppMaker\Tables\Columns\TextColumn;
use AppMaker\Tables\Filters\SelectFilter;
use AppMaker\Tables\Table;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserResource extends ResourceBase
{
    protected ?string $model = User::class;
    protected ?string $uri = 'users';

    public function table(): Table
    {
        return Table::make()
            ->heading('Users')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),

                BadgeColumn::make('role')
                    ->colors([
                        'admin' => 'blue',
                        'user' => 'gray',
                    ]),

                IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->formatStateUsing(fn ($state) => !is_null($state)),

                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'user' => 'User',
                    ]),
            ])
            ->recordActions([
                Action::make('verify')
                    ->icon('heroicons:check-badge')
                    ->color('green')
                    ->visible(fn ($record) => is_null($record->email_verified_at))
                    ->action(fn ($record) => $record->markEmailAsVerified()),
            ]);
    }

    public function form(): Form
    {
        return Form::make()
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->rules(['unique:users,email']),

                TextInput::make('password')
                    ->password()
                    ->required()
                    ->rules(['min:8', 'confirmed']),

                TextInput::make('password_confirmation')
                    ->password()
                    ->required(),

                Select::make('role')
                    ->required()
                    ->options([
                        'admin' => 'Administrator',
                        'user' => 'User',
                    ])
                    ->default('user'),

                Checkbox::make('email_verified')
                    ->label('Email Verified')
                    ->helperText('Mark this user as verified'),
            ]);
    }
}
```

## Example 4: Task Management

Task tracking system with assignments and priorities.

```php
public function table(): Table
{
    return Table::make()
        ->heading('Tasks')
        ->columns([
            TextColumn::make('title')
                ->searchable()
                ->limit(40),

            TextColumn::make('assigned_to.name')
                ->label('Assigned To'),

            BadgeColumn::make('priority')
                ->colors([
                    'low' => 'gray',
                    'medium' => 'yellow',
                    'high' => 'red',
                ]),

            BadgeColumn::make('status')
                ->colors([
                    'todo' => 'gray',
                    'in_progress' => 'blue',
                    'review' => 'yellow',
                    'done' => 'green',
                ]),

            TextColumn::make('due_date')
                ->formatStateUsing(fn ($state) => $state->format('M d, Y')),
        ])
        ->recordActions([
            Action::make('start')
                ->icon('tabler:play')
                ->visible(fn ($record) => $record->status === 'todo')
                ->action(fn ($record) => $record->update(['status' => 'in_progress'])),

            Action::make('complete')
                ->icon('heroicons:check-circle')
                ->color('green')
                ->visible(fn ($record) => $record->status === 'in_progress')
                ->action(fn ($record) => $record->complete()),
        ]);
}
```

## Next Steps

- [Advanced Customization](./06-advanced.md)
- [API Reference](./08-api-reference.md)
- [Icons Guide](./09-icons.md)
