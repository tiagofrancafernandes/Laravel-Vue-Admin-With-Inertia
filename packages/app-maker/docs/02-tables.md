# Tables

Tables are the heart of AppMaker's list pages. They provide a declarative way to define how your data is displayed, filtered, sorted, and acted upon.

## Basic Usage

```php
use AppMaker\Tables\Table;
use AppMaker\Tables\Columns\TextColumn;

public function table(): Table
{
    return Table::make()
        ->heading('My Records')
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('email'),
        ]);
}
```

## Table Configuration

### Heading

Set the table title:

```php
Table::make()->heading('Users List')
```

### Striped Rows

Enable alternating row colors:

```php
Table::make()->striped(true) // default is true
```

### Selectable Rows

Enable row selection for bulk actions:

```php
Table::make()->selectable(true) // default is true
```

### Pagination

Configure pagination options:

```php
Table::make()
    ->paginated([10, 25, 50, 100]) // Available options
    ->defaultPaginationPageOption(25) // Default per page
```

Disable pagination:

```php
Table::make()->paginated(false)
```

### Default Sorting

Set initial sort column and direction:

```php
Table::make()->defaultSort('created_at', 'desc')
```

## Columns

AppMaker provides several column types for different data presentations.

### TextColumn

Display text data:

```php
TextColumn::make('name')
    ->label('Full Name')          // Custom label
    ->searchable()                 // Enable search
    ->sortable()                   // Enable sorting
    ->limit(50)                    // Truncate after 50 chars
    ->wrap(true)                   // Allow text wrapping
    ->copyable()                   // Add copy-to-clipboard button
    ->formatStateUsing(fn ($state) => ucwords($state)) // Format value
```

### IconColumn

Display icons or boolean values:

```php
IconColumn::make('is_active')
    ->boolean()                    // Boolean display
    ->trueIcon('check-circle', 'green')
    ->falseIcon('x-circle', 'red')
```

### BadgeColumn

Display values as colored badges:

```php
BadgeColumn::make('status')
    ->colors([
        'pending' => 'yellow',
        'approved' => 'green',
        'rejected' => 'red',
        'draft' => 'gray',
    ])
```

### ImageColumn

Display images:

```php
ImageColumn::make('avatar')
    ->disk('public')
    ->rounded()
    ->width(40)
    ->height(40)
```

### Relationships

Access relationship data:

```php
TextColumn::make('author.name')
    ->label('Author')
    ->searchable(false) // Disable search on relationships
```

AppMaker automatically eager loads relationships to prevent N+1 queries.

### Custom Formatting

Transform column values before display:

```php
TextColumn::make('price')
    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state, 2, ',', '.'))

TextColumn::make('created_at')
    ->formatStateUsing(fn ($state) => $state->diffForHumans())
```

### Conditional Visibility

Hide columns based on conditions:

```php
TextColumn::make('admin_notes')
    ->visible(fn () => auth()->user()->isAdmin())

TextColumn::make('internal_code')
    ->hidden(fn () => !auth()->user()->can('view-internal-data'))
```

## Filters

Add filters to help users narrow down results.

### SelectFilter

Filter by specific values:

```php
use AppMaker\Tables\Filters\SelectFilter;

SelectFilter::make('status')
    ->options([
        'active' => 'Active',
        'inactive' => 'Inactive',
        'pending' => 'Pending',
    ])
    ->default('active')
    ->multiple(false) // Single selection
```

With custom query:

```php
SelectFilter::make('role')
    ->options(fn () => Role::pluck('name', 'id'))
    ->query(fn (Builder $query, $value) => 
        $query->whereHas('roles', fn ($q) => $q->where('id', $value))
    )
```

### DateFilter

Filter by date:

```php
use AppMaker\Tables\Filters\DateFilter;

DateFilter::make('created_after')
    ->label('Created After')
    ->minDate(now()->subYear()->format('Y-m-d'))
    ->maxDate(now()->format('Y-m-d'))
    ->query(fn (Builder $query, $value) => 
        $query->whereDate('created_at', '>=', $value)
    )
```

### BooleanFilter

Filter by true/false:

```php
use AppMaker\Tables\Filters\BooleanFilter;

BooleanFilter::make('is_featured')
    ->trueLabel('Featured Only')
    ->falseLabel('Not Featured')
```

### Custom Filters

Create custom filter logic:

```php
use AppMaker\Tables\Filters\Filter;

Filter::make('popular')
    ->label('Popular Posts')
    ->query(fn (Builder $query) => 
        $query->where('views', '>', 1000)
    )
```

## Search

Enable global search across multiple columns:

```php
Table::make()
    ->columns([
        TextColumn::make('title')->searchable(),
        TextColumn::make('description')->searchable(),
        TextColumn::make('author.name')->searchable(false), // Exclude
    ])
```

AppMaker automatically handles:
- Direct column search
- Relationship column search
- Case-insensitive matching
- Wildcard matching

## Actions

### Record Actions

Actions that operate on individual rows:

```php
use AppMaker\Actions\Action;

Table::make()
    ->recordActions([
        Action::make('approve')
            ->label('Approve')
            ->icon('check')
            ->color('green')
            ->action(fn ($record) => $record->approve())
            ->visible(fn ($record) => $record->isPending())
            ->requiresConfirmation(
                true,
                'Approve Record?',
                'This action cannot be undone.'
            ),
    ])
```

### Bulk Actions

Actions that operate on multiple selected rows:

```php
use AppMaker\Actions\BulkAction;
use AppMaker\Actions\BulkActionGroup;
use AppMaker\Actions\DeleteBulkAction;

Table::make()
    ->bulkActions([
        BulkActionGroup::make([
            BulkAction::make('publish')
                ->action(fn ($records) => $records->each->publish()),
                
            BulkAction::make('archive')
                ->action(fn ($records) => $records->each->archive()),
                
            DeleteBulkAction::make(), // Pre-built delete action
        ]),
    ])
```

## Complete Example

```php
public function table(): Table
{
    return Table::make()
        ->heading('Blog Posts')
        ->striped(true)
        ->selectable(true)
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
                ->limit(50)
                ->copyable(),
                
            TextColumn::make('author.name')
                ->label('Author')
                ->searchable(false),
                
            BadgeColumn::make('status')
                ->colors([
                    'draft' => 'gray',
                    'published' => 'green',
                    'archived' => 'red',
                ])
                ->sortable(),
                
            IconColumn::make('is_featured')
                ->boolean()
                ->sortable(),
                
            TextColumn::make('views')
                ->label('Views')
                ->formatStateUsing(fn ($state) => number_format($state))
                ->sortable(),
                
            TextColumn::make('created_at')
                ->label('Published')
                ->formatStateUsing(fn ($state) => $state->format('M d, Y'))
                ->sortable(),
        ])
        ->filters([
            SelectFilter::make('status')
                ->options([
                    'draft' => 'Draft',
                    'published' => 'Published',
                    'archived' => 'Archived',
                ]),
                
            SelectFilter::make('author_id')
                ->label('Author')
                ->options(fn () => User::pluck('name', 'id')),
                
            Filter::make('featured')
                ->label('Featured Only')
                ->query(fn ($query) => $query->where('is_featured', true)),
        ])
        ->recordActions([
            Action::make('publish')
                ->visible(fn ($record) => $record->status === 'draft')
                ->action(fn ($record) => $record->publish()),
                
            Action::make('feature')
                ->visible(fn ($record) => !$record->is_featured)
                ->action(fn ($record) => $record->update(['is_featured' => true])),
        ])
        ->bulkActions([
            BulkActionGroup::make([
                BulkAction::make('publish')
                    ->action(fn ($records) => $records->each->publish()),
                    
                DeleteBulkAction::make(),
            ]),
        ]);
}
```

## Performance Tips

### Eager Loading

AppMaker automatically detects and eager loads relationships:

```php
TextColumn::make('author.name')  // Automatically eager loads 'author'
TextColumn::make('category.title') // Automatically eager loads 'category'
```

### Search Optimization

For better search performance on large datasets:

```php
// Add indexes to searchable columns
Schema::table('posts', function (Blueprint $table) {
    $table->index('title');
    $table->index('description');
});
```

### Pagination

Always use pagination for large datasets:

```php
Table::make()
    ->paginated([25, 50, 100]) // Don't include 'all' option for large tables
    ->defaultPaginationPageOption(25)
```

## Next Steps

- [Learn about Forms](./03-forms.md)
- [Explore Actions](./04-actions.md)
- [Advanced Table Customization](./06-advanced.md)
