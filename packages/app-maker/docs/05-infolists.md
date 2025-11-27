# InfoLists

InfoLists display record details in a structured, read-only format on show pages.

## Basic Usage

```php
use AppMaker\InfoLists\InfoList;
use AppMaker\InfoLists\Entries\TextEntry;

public function infoList(): InfoList
{
    return InfoList::make()
        ->schema([
            TextEntry::make('name'),
            TextEntry::make('email'),
        ]);
}
```

## Configuration

### Layout Columns

Configure the grid layout:

```php
InfoList::make()
    ->columns(2) // 2-column layout
    ->schema([
        TextEntry::make('name'),
        TextEntry::make('email'),
        TextEntry::make('bio')->columnSpan(2), // Full width
    ])
```

## Entry Types

### TextEntry

Display text data:

```php
TextEntry::make('title')
    ->label('Post Title')
    ->columnSpan(1)
```

**With Badge:**

```php
TextEntry::make('status')
    ->badge()
    ->colors([
        'draft' => 'gray',
        'published' => 'green',
        'archived' => 'red',
    ])
```

**Copyable:**

```php
TextEntry::make('api_key')
    ->copyable()
    ->label('API Key')
```

### IconEntry

Display icons or boolean values:

```php
use AppMaker\InfoLists\Entries\IconEntry;

IconEntry::make('is_active')
    ->label('Status')
    ->boolean()
    ->trueIcon('check-circle', 'green')
    ->falseIcon('x-circle', 'red')
```

### ImageEntry

Display images:

```php
use AppMaker\InfoLists\Entries\ImageEntry;

ImageEntry::make('avatar')
    ->label('Profile Picture')
    ->disk('public')
    ->rounded()
    ->width(200)
    ->height(200)
```

## Formatting Values

### Date and Time

```php
TextEntry::make('created_at')
    ->dateTime('M d, Y H:i') // Format: Jan 15, 2024 14:30

TextEntry::make('published_at')
    ->date('F j, Y') // Format: January 15, 2024
```

### Custom Formatting

```php
TextEntry::make('price')
    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state, 2, ',', '.'))

TextEntry::make('views')
    ->formatStateUsing(fn ($state) => number_format($state) . ' views')
```

## Relationships

Access relationship data:

```php
TextEntry::make('author.name')
    ->label('Author')

TextEntry::make('category.title')
    ->label('Category')
```

## Column Spanning

Control entry width:

```php
InfoList::make()
    ->columns(3)
    ->schema([
        TextEntry::make('name'), // 1 column
        TextEntry::make('email'), // 1 column
        TextEntry::make('phone'), // 1 column
        TextEntry::make('bio')->columnSpan(3), // Full width
        TextEntry::make('address')->columnSpan(2), // 2/3 width
        TextEntry::make('city'), // 1/3 width
    ])
```

## Complete Example

```php
use AppMaker\InfoLists\Entries\IconEntry;
use AppMaker\InfoLists\Entries\ImageEntry;
use AppMaker\InfoLists\Entries\TextEntry;
use AppMaker\InfoLists\InfoList;

public function infoList(): InfoList
{
    return InfoList::make()
        ->columns(2)
        ->schema([
            // Header Image
            ImageEntry::make('cover_image')
                ->label('Cover Image')
                ->disk('public')
                ->width(600)
                ->height(300)
                ->columnSpan(2),

            // Basic Info
            TextEntry::make('title')
                ->label('Title')
                ->columnSpan(2),

            TextEntry::make('author.name')
                ->label('Author'),

            TextEntry::make('category.name')
                ->label('Category'),

            // Status
            TextEntry::make('status')
                ->badge()
                ->colors([
                    'draft' => 'gray',
                    'reviewing' => 'yellow',
                    'published' => 'green',
                    'archived' => 'red',
                ]),

            IconEntry::make('is_featured')
                ->label('Featured')
                ->boolean(),

            // Dates
            TextEntry::make('published_at')
                ->label('Published')
                ->dateTime('M d, Y H:i'),

            TextEntry::make('created_at')
                ->label('Created')
                ->dateTime('M d, Y H:i'),

            // Stats
            TextEntry::make('views')
                ->label('Views')
                ->formatStateUsing(fn ($state) => number_format($state)),

            TextEntry::make('likes')
                ->label('Likes')
                ->formatStateUsing(fn ($state) => number_format($state)),

            // Content
            TextEntry::make('excerpt')
                ->label('Excerpt')
                ->columnSpan(2),

            TextEntry::make('content')
                ->label('Content')
                ->columnSpan(2),

            // SEO
            TextEntry::make('meta_title')
                ->label('SEO Title')
                ->columnSpan(2),

            TextEntry::make('meta_description')
                ->label('SEO Description')
                ->columnSpan(2),
        ]);
}
```

## Best Practices

### 1. Organize Related Fields

Group related information together:

```php
InfoList::make()
    ->schema([
        // Personal Info
        TextEntry::make('name'),
        TextEntry::make('email'),
        TextEntry::make('phone'),
        
        // Address
        TextEntry::make('address')->columnSpan(2),
        TextEntry::make('city'),
        TextEntry::make('state'),
        
        // Dates
        TextEntry::make('created_at')->dateTime(),
        TextEntry::make('updated_at')->dateTime(),
    ])
```

### 2. Use Appropriate Entry Types

```php
// Use IconEntry for booleans
IconEntry::make('is_verified')->boolean()

// Use badge for status
TextEntry::make('status')->badge()

// Use ImageEntry for images
ImageEntry::make('avatar')->rounded()
```

### 3. Format Values for Readability

```php
// Good
TextEntry::make('created_at')->dateTime('M d, Y')
TextEntry::make('price')->formatStateUsing(fn ($state) => '$' . number_format($state, 2))

// Bad
TextEntry::make('created_at') // Shows raw timestamp
TextEntry::make('price') // Shows raw number
```

### 4. Provide Clear Labels

```php
// Good
TextEntry::make('author.name')->label('Written By')
TextEntry::make('published_at')->label('Publication Date')

// Acceptable
TextEntry::make('name') // Uses auto-generated "Name"
```

### 5. Use Column Spanning Effectively

```php
// Full-width for long text
TextEntry::make('description')->columnSpan(2)

// Group small fields side-by-side
TextEntry::make('city')
TextEntry::make('state')
```

## Fallback Display

If you don't define an `infoList()`, AppMaker automatically displays all record fields:

```php
// No infoList defined
public function infoList(): ?InfoList
{
    return null; // Shows all fields automatically
}
```

## Next Steps

- [Advanced Customization](./06-advanced.md)
- [Practical Examples](./07-examples.md)
- [API Reference](./08-api-reference.md)
