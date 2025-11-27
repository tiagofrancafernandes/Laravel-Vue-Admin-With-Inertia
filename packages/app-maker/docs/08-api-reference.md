# API Reference

Complete reference for all AppMaker classes and methods.

## Table of Contents

- [ResourceBase](#resourcebase)
- [Table](#table)
- [Columns](#columns)
- [Filters](#filters)
- [Actions](#actions)
- [Form](#form)
- [Form Components](#form-components)
- [InfoList](#infolist)
- [InfoList Entries](#infolist-entries)

---

## ResourceBase

Base class for all resources.

### Properties

```php
protected ?string $model = null;
protected ?string $uri = null;
protected ?string $label = null;
protected ?string $pluralLabel = null;
```

### Methods

#### `table(): Table`

Define the resource table configuration.

```php
public function table(): Table
{
    return Table::make()
        ->columns([/* ... */]);
}
```

#### `form(): ?Form`

Define the resource form configuration.

```php
public function form(): ?Form
{
    return Form::make()
        ->schema([/* ... */]);
}
```

#### `infoList(): ?InfoList`

Define the resource detail view configuration.

```php
public function infoList(): ?InfoList
{
    return InfoList::make()
        ->schema([/* ... */]);
}
```

#### `getModel(): string`

Get the model class name.

#### `getUri(): string`

Get the resource URI.

#### `getLabel(): string`

Get the singular label.

#### `getPluralLabel(): string`

Get the plural label.

---

## Table

Configure table display and behavior.

### Methods

#### `static make(): static`

Create a new table instance.

```php
Table::make()
```

#### `heading(?string $heading): static`

Set the table heading.

```php
->heading('Blog Posts')
```

#### `columns(array $columns): static`

Set the table columns.

```php
->columns([
    TextColumn::make('name'),
    TextColumn::make('email'),
])
```

#### `filters(array $filters): static`

Set the table filters.

```php
->filters([
    SelectFilter::make('status'),
    DateFilter::make('created_at'),
])
```

#### `recordActions(array $actions): static`

Set actions for individual records.

```php
->recordActions([
    Action::make('view'),
    Action::make('edit'),
])
```

#### `bulkActions(array $actions): static`

Set bulk actions for selected records.

```php
->bulkActions([
    BulkActionGroup::make([
        BulkAction::make('archive'),
        DeleteBulkAction::make(),
    ]),
])
```

#### `searchable(bool $searchable = true): static`

Enable/disable search.

```php
->searchable(true)
```

#### `searchPlaceholder(string $placeholder): static`

Set search placeholder text.

```php
->searchPlaceholder('Search posts...')
```

#### `striped(bool $striped = true): static`

Enable/disable striped rows.

```php
->striped(true)
```

#### `hoverable(bool $hoverable = true): static`

Enable/disable row hover effect.

```php
->hoverable(true)
```

#### `defaultSort(string $column, string $direction = 'asc'): static`

Set default sort column and direction.

```php
->defaultSort('created_at', 'desc')
```

#### `defaultPerPage(int $perPage): static`

Set default number of records per page.

```php
->defaultPerPage(25)
```

#### `perPageOptions(array $options): static`

Set available per-page options.

```php
->perPageOptions([10, 25, 50, 100])
```

#### `modifyQueryUsing(\Closure $callback): static`

Modify the query before execution.

```php
->modifyQueryUsing(function ($query) {
    return $query->where('status', 'published');
})
```

---

## Columns

### TextColumn

Display text data.

#### Methods

```php
TextColumn::make(string $name): static
->label(string $label): static
->sortable(bool $sortable = true): static
->searchable(bool $searchable = true): static
->limit(int $limit): static
->copyable(bool $copyable = true): static
->formatStateUsing(\Closure $callback): static
```

#### Example

```php
TextColumn::make('title')
    ->label('Post Title')
    ->searchable()
    ->sortable()
    ->limit(50)
    ->copyable()
```

### IconColumn

Display icons or boolean values.

#### Methods

```php
IconColumn::make(string $name): static
->label(string $label): static
->boolean(bool $boolean = true): static
->trueIcon(string $icon, string $color = 'green'): static
->falseIcon(string $icon, string $color = 'gray'): static
->icon(string $icon): static
->color(string $color): static
->sortable(bool $sortable = true): static
```

#### Example

```php
IconColumn::make('is_active')
    ->boolean()
    ->trueIcon('heroicons:check-circle', 'green')
    ->falseIcon('heroicons:x-circle', 'red')
    ->sortable()
```

### BadgeColumn

Display badges with colors.

#### Methods

```php
BadgeColumn::make(string $name): static
->label(string $label): static
->colors(array $colors): static
->sortable(bool $sortable = true): static
```

#### Example

```php
BadgeColumn::make('status')
    ->colors([
        'draft' => 'gray',
        'published' => 'green',
        'archived' => 'red',
    ])
    ->sortable()
```

### ImageColumn

Display images.

#### Methods

```php
ImageColumn::make(string $name): static
->label(string $label): static
->disk(string $disk): static
->width(int $width): static
->height(int $height): static
->rounded(bool $rounded = true): static
```

#### Example

```php
ImageColumn::make('avatar')
    ->disk('public')
    ->width(50)
    ->height(50)
    ->rounded()
```

---

## Filters

### Filter

Basic filter with custom query.

#### Methods

```php
Filter::make(string $name): static
->label(string $label): static
->query(\Closure $callback): static
```

#### Example

```php
Filter::make('featured')
    ->label('Featured Only')
    ->query(fn ($query) => $query->where('is_featured', true))
```

### SelectFilter

Dropdown filter.

#### Methods

```php
SelectFilter::make(string $name): static
->label(string $label): static
->options(array|\Closure $options): static
->multiple(bool $multiple = true): static
```

#### Example

```php
SelectFilter::make('status')
    ->label('Status')
    ->options([
        'draft' => 'Draft',
        'published' => 'Published',
    ])
    ->multiple()
```

### DateFilter

Date range filter.

#### Methods

```php
DateFilter::make(string $name): static
->label(string $label): static
->withTime(bool $withTime = true): static
```

#### Example

```php
DateFilter::make('created_at')
    ->label('Created Date')
    ->withTime()
```

### BooleanFilter

True/false filter.

#### Methods

```php
BooleanFilter::make(string $name): static
->label(string $label): static
->trueLabel(string $label): static
->falseLabel(string $label): static
```

#### Example

```php
BooleanFilter::make('is_active')
    ->label('Status')
    ->trueLabel('Active')
    ->falseLabel('Inactive')
```

---

## Actions

### Action

Individual record actions.

#### Methods

```php
Action::make(string $name): static
->label(string $label): static
->icon(string $icon): static
->color(string $color): static
->visible(bool|\Closure $visible): static
->disabled(bool|\Closure $disabled): static
->action(\Closure $callback): static
->requiresConfirmation(bool $requires = true, ?string $title = null, ?string $text = null): static
->authorize(bool|\Closure $authorize): static
```

#### Example

```php
Action::make('publish')
    ->label('Publish Post')
    ->icon('tabler:send')
    ->color('green')
    ->visible(fn ($record) => $record->status === 'draft')
    ->requiresConfirmation(
        true,
        'Publish Post?',
        'This will make the post visible to everyone.'
    )
    ->action(function ($record) {
        $record->publish();
    })
```

### BulkAction

Actions on multiple selected records.

#### Methods

```php
BulkAction::make(string $name): static
->label(string $label): static
->icon(string $icon): static
->color(string $color): static
->action(\Closure $callback): static
->requiresConfirmation(bool $requires = true, ?string $title = null, ?string $text = null): static
->authorize(bool|\Closure $authorize): static
```

#### Example

```php
BulkAction::make('archive')
    ->label('Archive Selected')
    ->icon('tabler:archive')
    ->color('yellow')
    ->requiresConfirmation()
    ->action(function ($records) {
        $records->each->archive();
    })
```

### BulkActionGroup

Group multiple bulk actions.

#### Methods

```php
BulkActionGroup::make(array $actions): static
```

#### Example

```php
BulkActionGroup::make([
    BulkAction::make('archive'),
    BulkAction::make('publish'),
    DeleteBulkAction::make(),
])
```

### DeleteBulkAction

Pre-built bulk delete action.

#### Methods

```php
DeleteBulkAction::make(): static
```

#### Example

```php
DeleteBulkAction::make()
```

---

## Form

Configure forms for create and edit pages.

### Methods

#### `static make(): static`

Create a new form instance.

```php
Form::make()
```

#### `schema(array $components): static`

Set form components.

```php
->schema([
    TextInput::make('name'),
    Textarea::make('description'),
])
```

#### `columns(int|array $columns): static`

Set form grid columns.

```php
->columns(2)  // 2 columns
->columns(['md' => 1, 'lg' => 2])  // Responsive
```

#### `beforeSave(\Closure $callback): static`

Hook before saving data.

```php
->beforeSave(function ($data, $record) {
    $data['slug'] = Str::slug($data['title']);
    return $data;
})
```

#### `afterSave(\Closure $callback): static`

Hook after saving data.

```php
->afterSave(function ($record, $data) {
    Cache::forget("post_{$record->id}");
})
```

---

## Form Components

### TextInput

Text input field.

#### Methods

```php
TextInput::make(string $name): static
->label(string $label): static
->placeholder(string $placeholder): static
->helperText(string $text): static
->default(mixed $default): static
->required(bool $required = true): static
->disabled(bool|\Closure $disabled = true): static
->visible(bool|\Closure $visible = true): static
->columnSpan(int $span): static
->rules(array $rules): static
->maxLength(int $length): static
->email(): static
->url(): static
->tel(): static
->numeric(): static
->password(): static
```

#### Example

```php
TextInput::make('email')
    ->label('Email Address')
    ->email()
    ->required()
    ->placeholder('user@example.com')
    ->helperText('We will never share your email')
```

### Textarea

Multi-line text input.

#### Methods

```php
Textarea::make(string $name): static
->label(string $label): static
->placeholder(string $placeholder): static
->helperText(string $text): static
->default(mixed $default): static
->required(bool $required = true): static
->disabled(bool|\Closure $disabled = true): static
->visible(bool|\Closure $visible = true): static
->columnSpan(int $span): static
->rules(array $rules): static
->rows(int $rows): static
->maxLength(int $length): static
```

#### Example

```php
Textarea::make('description')
    ->label('Description')
    ->rows(5)
    ->maxLength(500)
    ->required()
```

### Select

Dropdown selection.

#### Methods

```php
Select::make(string $name): static
->label(string $label): static
->placeholder(string $placeholder): static
->helperText(string $text): static
->default(mixed $default): static
->required(bool $required = true): static
->disabled(bool|\Closure $disabled = true): static
->visible(bool|\Closure $visible = true): static
->columnSpan(int $span): static
->rules(array $rules): static
->options(array|\Closure $options): static
->searchable(bool $searchable = true): static
->multiple(bool $multiple = true): static
```

#### Example

```php
Select::make('category_id')
    ->label('Category')
    ->options(fn () => Category::pluck('name', 'id'))
    ->searchable()
    ->required()
```

### Checkbox

Checkbox input.

#### Methods

```php
Checkbox::make(string $name): static
->label(string $label): static
->helperText(string $text): static
->default(bool $default): static
->disabled(bool|\Closure $disabled = true): static
->visible(bool|\Closure $visible = true): static
->columnSpan(int $span): static
```

#### Example

```php
Checkbox::make('is_featured')
    ->label('Feature this post')
    ->helperText('Featured posts appear on the homepage')
    ->default(false)
```

### DatePicker

Date and time picker.

#### Methods

```php
DatePicker::make(string $name): static
->label(string $label): static
->placeholder(string $placeholder): static
->helperText(string $text): static
->default(mixed $default): static
->required(bool $required = true): static
->disabled(bool|\Closure $disabled = true): static
->visible(bool|\Closure $visible = true): static
->columnSpan(int $span): static
->rules(array $rules): static
->withTime(bool $withTime = true): static
->format(string $format): static
->minDate(string|\Closure $date): static
->maxDate(string|\Closure $date): static
```

#### Example

```php
DatePicker::make('published_at')
    ->label('Publish Date')
    ->withTime()
    ->format('Y-m-d H:i')
    ->minDate(now())
    ->required()
```

### FileUpload

File upload field.

#### Methods

```php
FileUpload::make(string $name): static
->label(string $label): static
->helperText(string $text): static
->default(mixed $default): static
->required(bool $required = true): static
->disabled(bool|\Closure $disabled = true): static
->visible(bool|\Closure $visible = true): static
->columnSpan(int $span): static
->rules(array $rules): static
->disk(string $disk): static
->directory(string $directory): static
->image(): static
->maxSize(int $kilobytes): static
->acceptedFileTypes(array $types): static
->multiple(bool $multiple = true): static
```

#### Example

```php
FileUpload::make('featured_image')
    ->label('Featured Image')
    ->image()
    ->disk('public')
    ->directory('posts/images')
    ->maxSize(2048)
    ->required()
```

---

## InfoList

Configure detail view display.

### Methods

#### `static make(): static`

Create a new info list instance.

```php
InfoList::make()
```

#### `schema(array $entries): static`

Set info list entries.

```php
->schema([
    TextEntry::make('title'),
    ImageEntry::make('avatar'),
])
```

#### `columns(int|array $columns): static`

Set grid columns.

```php
->columns(2)
->columns(['md' => 1, 'lg' => 2])
```

---

## InfoList Entries

### TextEntry

Display text data.

#### Methods

```php
TextEntry::make(string $name): static
->label(string $label): static
->columnSpan(int $span): static
->badge(): static
->colors(array $colors): static
->copyable(bool $copyable = true): static
->formatStateUsing(\Closure $callback): static
->dateTime(string $format = 'M d, Y H:i'): static
->date(string $format = 'M d, Y'): static
```

#### Example

```php
TextEntry::make('title')
    ->label('Post Title')
    ->copyable()
    ->columnSpan(2)
```

### IconEntry

Display icons or boolean values.

#### Methods

```php
IconEntry::make(string $name): static
->label(string $label): static
->columnSpan(int $span): static
->boolean(): static
->trueIcon(string $icon, string $color = 'green'): static
->falseIcon(string $icon, string $color = 'gray'): static
->icon(string $icon): static
->color(string $color): static
```

#### Example

```php
IconEntry::make('is_published')
    ->label('Published')
    ->boolean()
    ->trueIcon('heroicons:check-circle', 'green')
    ->falseIcon('heroicons:x-circle', 'gray')
```

### ImageEntry

Display images.

#### Methods

```php
ImageEntry::make(string $name): static
->label(string $label): static
->columnSpan(int $span): static
->disk(string $disk): static
->width(int $width): static
->height(int $height): static
->rounded(bool $rounded = true): static
```

#### Example

```php
ImageEntry::make('cover_image')
    ->label('Cover Image')
    ->disk('public')
    ->width(600)
    ->height(300)
    ->columnSpan(2)
```

---

## Color Options

Available colors for badges, icons, and actions:

- `gray` - Default/neutral
- `red` - Danger/delete
- `yellow` - Warning/pending
- `green` - Success/active
- `blue` - Info/primary

## Icon Format

Icons use the Iconify format: `{set}:{icon}`

**Examples:**
```php
'tabler:check'
'tabler:edit'
'heroicons:trash'
'heroicons:check-circle'
```

See [Icons Guide](./09-icons.md) for complete reference.

---

## Validation Rules

Standard Laravel validation rules are supported:

```php
->rules([
    'required',
    'email',
    'min:3',
    'max:255',
    'unique:users,email',
    'regex:/^[a-z]+$/',
    function ($attribute, $value, $fail) {
        if ($value === 'invalid') {
            $fail('The value is invalid.');
        }
    },
])
```

---

## Next Steps

- [Getting Started](./01-getting-started.md)
- [Practical Examples](./07-examples.md)
- [Advanced Customization](./06-advanced.md)
