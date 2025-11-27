# Forms

Forms in AppMaker provide a declarative way to create and edit records with built-in validation, field types, and automatic data handling.

## Basic Usage

```php
use AppMaker\Forms\Form;
use AppMaker\Forms\Components\TextInput;
use AppMaker\Forms\Components\Textarea;

public function form(): Form
{
    return Form::make()
        ->schema([
            TextInput::make('name')->required(),
            Textarea::make('description'),
        ]);
}
```

## Form Configuration

### Heading

Set the form title:

```php
Form::make()->heading('Create Post')
```

### Layout Columns

Configure the form grid layout:

```php
Form::make()
    ->columns(2) // 2-column layout
    ->schema([
        TextInput::make('first_name'),  // Takes 1 column
        TextInput::make('last_name'),   // Takes 1 column
        Textarea::make('bio')->columnSpan(2), // Spans 2 columns
    ])
```

### Submit/Cancel Labels

Customize button labels:

```php
Form::make()
    ->submitLabel('Save Changes')
    ->cancelLabel('Go Back')
```

## Form Components

### TextInput

Standard text input field:

```php
TextInput::make('name')
    ->label('Full Name')
    ->required()
    ->maxLength(255)
    ->minLength(3)
    ->placeholder('Enter your name')
    ->helperText('Your name as it appears on official documents')
    ->disabled(fn () => !auth()->user()->isAdmin())
    ->columnSpan(2)
```

**Input Types:**

```php
// Email
TextInput::make('email')
    ->email()
    ->required()

// Password
TextInput::make('password')
    ->password()
    ->required()

// URL
TextInput::make('website')
    ->url()

// Telephone
TextInput::make('phone')
    ->tel()

// Numeric
TextInput::make('age')
    ->numeric()
```

### Textarea

Multi-line text input:

```php
Textarea::make('content')
    ->required()
    ->rows(10)
    ->maxLength(5000)
    ->placeholder('Write your content here...')
    ->helperText('Supports plain text only')
```

### Select

Dropdown selection:

```php
Select::make('category_id')
    ->label('Category')
    ->required()
    ->options([
        1 => 'Technology',
        2 => 'Business',
        3 => 'Lifestyle',
    ])
    ->default(1)
    ->searchable() // Enable search in dropdown
    ->multiple(false) // Single selection
```

**Dynamic Options:**

```php
Select::make('category_id')
    ->options(fn () => Category::pluck('name', 'id')->toArray())

Select::make('tags')
    ->multiple()
    ->options(fn () => Tag::pluck('name', 'id'))
```

### Checkbox

Boolean checkbox:

```php
Checkbox::make('is_featured')
    ->label('Feature this post')
    ->helperText('Featured posts appear on the homepage')
    ->default(false)
```

### DatePicker

Date and time selection:

```php
use AppMaker\Forms\Components\DatePicker;

DatePicker::make('published_at')
    ->label('Publish Date')
    ->format('Y-m-d')
    ->minDate(now())
    ->maxDate(now()->addYear())
    ->required()

// With time
DatePicker::make('scheduled_at')
    ->withTime()
    ->format('Y-m-d H:i')
```

### FileUpload

File upload with preview:

```php
use AppMaker\Forms\Components\FileUpload;

FileUpload::make('cover_image')
    ->label('Cover Image')
    ->disk('public')
    ->directory('posts/covers')
    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
    ->maxSize(2048) // KB
    ->image() // Image-specific validation
    ->required()

// Multiple files
FileUpload::make('attachments')
    ->multiple()
    ->maxSize(5120)
```

## Validation

### Automatic Validation

AppMaker automatically generates validation rules based on component configuration:

```php
TextInput::make('email')
    ->required()        // Adds 'required' rule
    ->email()           // Adds 'email' rule
    ->maxLength(255)    // Adds 'max:255' rule
    ->minLength(5)      // Adds 'min:5' rule

TextInput::make('age')
    ->numeric()         // Adds 'numeric' rule

FileUpload::make('photo')
    ->image()           // Adds 'image' rule
    ->maxSize(2048)     // Adds 'max:2048' rule
```

### Custom Validation Rules

Add custom rules to components:

```php
TextInput::make('username')
    ->required()
    ->rules(['unique:users,username', 'alpha_dash'])

TextInput::make('password')
    ->password()
    ->rules('required|min:8|confirmed|regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)/')
```

### Form-Level Validation

Define validation at the form level:

```php
Form::make()
    ->schema([
        TextInput::make('email'),
        TextInput::make('password'),
    ])
    ->rules([
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed',
    ])
```

## Field Visibility

### Conditional Visibility

Show/hide fields based on conditions:

```php
TextInput::make('company_name')
    ->visible(fn () => auth()->user()->isBusinessAccount())

Select::make('department_id')
    ->visible(fn () => auth()->user()->isEmployee())
    ->options(fn () => Department::pluck('name', 'id'))
```

### Disabled Fields

Prevent editing:

```php
TextInput::make('email')
    ->disabled(fn () => !auth()->user()->isAdmin())

Select::make('role')
    ->disabled(fn () => auth()->user()->cannot('assign-roles'))
    ->options([...])
```

## Helper Text and Placeholders

### Helper Text

Provide additional guidance:

```php
TextInput::make('slug')
    ->helperText('URL-friendly version of the title (lowercase, no spaces)')

Textarea::make('meta_description')
    ->helperText('Recommended length: 150-160 characters for SEO')
```

### Placeholders

Show example values:

```php
TextInput::make('username')
    ->placeholder('john_doe123')

TextInput::make('website')
    ->placeholder('https://example.com')
```

## Complete Form Example

```php
use AppMaker\Forms\Form;
use AppMaker\Forms\Components\Checkbox;
use AppMaker\Forms\Components\DatePicker;
use AppMaker\Forms\Components\FileUpload;
use AppMaker\Forms\Components\Select;
use AppMaker\Forms\Components\Textarea;
use AppMaker\Forms\Components\TextInput;

public function form(): Form
{
    return Form::make()
        ->heading('Create Blog Post')
        ->columns(2)
        ->schema([
            // Basic Information
            TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->placeholder('Enter post title')
                ->columnSpan(2),

            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->rules(['alpha_dash', 'unique:posts,slug'])
                ->helperText('URL-friendly version of the title')
                ->columnSpan(2),

            // Content
            Textarea::make('excerpt')
                ->label('Short Description')
                ->maxLength(500)
                ->rows(3)
                ->helperText('Brief summary for previews (max 500 characters)')
                ->columnSpan(2),

            Textarea::make('content')
                ->label('Post Content')
                ->required()
                ->rows(15)
                ->columnSpan(2),

            // Media
            FileUpload::make('featured_image')
                ->label('Featured Image')
                ->image()
                ->disk('public')
                ->directory('posts/featured')
                ->maxSize(2048)
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->helperText('Recommended size: 1200x630px')
                ->columnSpan(2),

            // Categorization
            Select::make('category_id')
                ->label('Category')
                ->required()
                ->options(fn () => \App\Models\Category::pluck('name', 'id'))
                ->searchable(),

            Select::make('tags')
                ->label('Tags')
                ->multiple()
                ->options(fn () => \App\Models\Tag::pluck('name', 'id'))
                ->searchable(),

            // Status
            Select::make('status')
                ->required()
                ->options([
                    'draft' => 'Draft',
                    'reviewing' => 'Under Review',
                    'published' => 'Published',
                    'archived' => 'Archived',
                ])
                ->default('draft'),

            Select::make('author_id')
                ->label('Author')
                ->required()
                ->options(fn () => \App\Models\User::pluck('name', 'id'))
                ->default(fn () => auth()->id())
                ->disabled(fn () => !auth()->user()->isAdmin()),

            // Publishing
            DatePicker::make('published_at')
                ->label('Publish Date')
                ->withTime()
                ->format('Y-m-d H:i')
                ->helperText('Leave empty to publish immediately'),

            Checkbox::make('is_featured')
                ->label('Feature this post')
                ->helperText('Featured posts appear on homepage'),

            Checkbox::make('comments_enabled')
                ->label('Enable Comments')
                ->default(true),

            Checkbox::make('notify_subscribers')
                ->label('Notify Subscribers')
                ->helperText('Send email notification to all subscribers')
                ->visible(fn () => auth()->user()->can('send-notifications')),

            // SEO
            TextInput::make('meta_title')
                ->label('SEO Title')
                ->maxLength(60)
                ->helperText('Max 60 characters for search engines')
                ->columnSpan(2),

            Textarea::make('meta_description')
                ->label('SEO Description')
                ->maxLength(160)
                ->rows(3)
                ->helperText('Max 160 characters for search engines')
                ->columnSpan(2),
        ])
        ->submitLabel('Save Post')
        ->cancelLabel('Cancel');
}
```

## Form Submission

Forms are automatically handled by the ResourceController:

```php
// On create
public function store(Request $request, string $resource)
{
    $form = $resourceInstance->form();
    $validated = $request->validate($form->getValidationRules());
    $record = $modelClass::create($validated);
    // ...
}

// On update
public function update(Request $request, string $resource, $id)
{
    $form = $resourceInstance->form();
    $validated = $request->validate($form->getValidationRules());
    $record->update($validated);
    // ...
}
```

## Tips and Best Practices

### 1. Group Related Fields

Use `columnSpan` to organize related fields:

```php
// Full-width for important fields
TextInput::make('title')->columnSpan(2),

// Side-by-side for related fields
TextInput::make('first_name'),
TextInput::make('last_name'),
```

### 2. Provide Clear Labels and Help Text

```php
TextInput::make('slug')
    ->label('URL Slug')
    ->helperText('Lowercase letters, numbers, and hyphens only')
    ->placeholder('my-awesome-post')
```

### 3. Use Appropriate Input Types

```php
// Use email type for email validation
TextInput::make('email')->email()

// Use numeric for numbers
TextInput::make('price')->numeric()

// Use password for sensitive data
TextInput::make('password')->password()
```

### 4. Set Sensible Defaults

```php
Select::make('status')->default('draft')
Checkbox::make('is_active')->default(true)
DatePicker::make('published_at')->default(now())
```

### 5. Consider User Experience

```php
// Searchable selects for long lists
Select::make('country_id')
    ->searchable()
    ->options(fn () => Country::pluck('name', 'id'))

// Helper text for complex fields
Textarea::make('bio')
    ->helperText('Tell us about yourself in 2-3 sentences')
```

## Next Steps

- [Learn about Actions](./04-actions.md)
- [Explore InfoLists](./05-infolists.md)
- [Advanced Form Customization](./06-advanced.md)
