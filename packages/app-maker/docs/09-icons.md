# Icons

AppMaker uses Iconify for icons, providing access to thousands of icon sets through a unified API.

## Icon Packages

AppMaker comes with two icon sets pre-installed:

- **Tabler Icons** (@iconify-json/tabler) - Clean, minimal icons
- **Heroicons** (@iconify-json/heroicons) - Solid and outline icons by Tailwind

## Using Icons

### In Actions

```php
use AppMaker\Actions\Action;

Action::make('publish')
    ->icon('tabler:check') // Tabler icon
    ->color('green')

Action::make('delete')
    ->icon('heroicons:trash') // Heroicons icon
    ->color('red')

Action::make('edit')
    ->icon('tabler:edit') // Tabler edit icon
```

### In IconColumn

```php
use AppMaker\Tables\Columns\IconColumn;

IconColumn::make('status')
    ->icon('tabler:circle-check')
    ->color('green')

IconColumn::make('is_active')
    ->boolean()
    ->trueIcon('heroicons:check-circle', 'green')
    ->falseIcon('heroicons:x-circle', 'red')
```

## Available Icon Sets

### Tabler Icons

Popular icons from Tabler:

```php
'tabler:check'          // Checkmark
'tabler:x'              // Close/X
'tabler:edit'           // Edit/Pencil
'tabler:trash'          // Delete/Trash
'tabler:eye'            // View/Eye
'tabler:download'       // Download
'tabler:upload'         // Upload
'tabler:plus'           // Add/Plus
'tabler:minus'          // Subtract
'tabler:search'         // Search
'tabler:settings'       // Settings/Gear
'tabler:user'           // User
'tabler:mail'           // Email
'tabler:star'           // Star
'tabler:heart'          // Heart/Like
'tabler:share'          // Share
'tabler:archive'        // Archive
'tabler:refresh'        // Refresh
'tabler:dots-vertical'  // Menu (vertical dots)
'tabler:chevron-right'  // Arrow right
```

### Heroicons

Popular icons from Heroicons:

```php
// Solid variants
'heroicons:check-circle'
'heroicons:x-circle'
'heroicons:trash'
'heroicons:pencil'
'heroicons:eye'
'heroicons:download'
'heroicons:upload'
'heroicons:plus-circle'
'heroicons:magnifying-glass'
'heroicons:cog'
'heroicons:user'
'heroicons:envelope'
'heroicons:star'
'heroicons:heart'
'heroicons:share'
'heroicons:archive-box'
'heroicons:arrow-path'

// Outline variants (add -outline)
'heroicons:check-circle-outline'
'heroicons:x-circle-outline'
'heroicons:trash-outline'
```

## Icon Browser

To explore all available icons:

- **Tabler Icons**: https://tabler-icons.io/
- **Heroicons**: https://heroicons.com/
- **Iconify**: https://icon-sets.iconify.design/

## Adding More Icon Sets

To add additional icon sets, install the corresponding package:

```bash
npm install @iconify-json/mdi  # Material Design Icons
npm install @iconify-json/fa6-solid  # Font Awesome 6
npm install @iconify-json/lucide  # Lucide icons
```

Then use them with the appropriate prefix:

```php
Action::make('save')
    ->icon('mdi:content-save')

Action::make('print')
    ->icon('fa6-solid:print')

Action::make('home')
    ->icon('lucide:home')
```

## Icon Colors

Icons support the following colors:

```php
->color('gray')    // Default
->color('red')     // Danger/Delete
->color('yellow')  // Warning
->color('green')   // Success
->color('blue')    // Info/Primary
```

## Examples by Context

### Table Actions

```php
Table::make()
    ->recordActions([
        Action::make('view')
            ->icon('heroicons:eye')
            ->color('blue'),
            
        Action::make('edit')
            ->icon('tabler:edit')
            ->color('gray'),
            
        Action::make('delete')
            ->icon('heroicons:trash')
            ->color('red'),
    ])
```

### Bulk Actions

```php
BulkActionGroup::make([
    BulkAction::make('archive')
        ->icon('tabler:archive')
        ->color('yellow'),
        
    BulkAction::make('export')
        ->icon('heroicons:arrow-down-tray')
        ->color('blue'),
        
    DeleteBulkAction::make()
        ->icon('heroicons:trash'),
])
```

### Status Icons

```php
IconColumn::make('is_verified')
    ->boolean()
    ->trueIcon('heroicons:check-badge', 'green')
    ->falseIcon('heroicons:x-circle', 'gray')

IconColumn::make('has_errors')
    ->boolean()
    ->trueIcon('tabler:alert-circle', 'red')
    ->falseIcon('tabler:circle-check', 'green')
```

### Workflow Actions

```php
// Approval workflow
Action::make('approve')
    ->icon('heroicons:check-circle')
    ->color('green')

Action::make('reject')
    ->icon('heroicons:x-circle')
    ->color('red')

Action::make('review')
    ->icon('heroicons:eye')
    ->color('yellow')

// Publishing
Action::make('publish')
    ->icon('tabler:send')
    ->color('green')

Action::make('schedule')
    ->icon('tabler:calendar')
    ->color('blue')

Action::make('draft')
    ->icon('tabler:file')
    ->color('gray')
```

## Best Practices

### 1. Use Consistent Icon Sets

Stick to one icon set for consistency:

```php
// Good - all Tabler
Action::make('edit')->icon('tabler:edit')
Action::make('delete')->icon('tabler:trash')
Action::make('view')->icon('tabler:eye')

// Avoid mixing unless necessary
Action::make('edit')->icon('tabler:edit')
Action::make('delete')->icon('heroicons:trash')  // Mixed
```

### 2. Match Icon to Action

Choose icons that clearly represent the action:

```php
// Clear and intuitive
Action::make('send_email')->icon('heroicons:envelope')
Action::make('download')->icon('tabler:download')
Action::make('star')->icon('heroicons:star')

// Confusing
Action::make('send_email')->icon('tabler:trash')  // Wrong icon
```

### 3. Use Color Meaningfully

```php
// Good - colors match intent
Action::make('approve')->icon('tabler:check')->color('green')
Action::make('reject')->icon('tabler:x')->color('red')
Action::make('edit')->icon('tabler:edit')->color('blue')

// Confusing
Action::make('approve')->icon('tabler:check')->color('red')  // Wrong color
```

### 4. Provide Icons for All Actions

```php
// Good - all actions have icons
Action::make('publish')->icon('tabler:send')
Action::make('archive')->icon('tabler:archive')
Action::make('duplicate')->icon('tabler:copy')

// Missing icons makes UI inconsistent
Action::make('publish')->icon('tabler:send')
Action::make('archive')  // No icon
```

## Icon Reference by Action Type

### CRUD Operations

```php
'tabler:plus'        // Create
'tabler:edit'        // Update
'heroicons:trash'    // Delete
'tabler:eye'         // View/Read
'tabler:copy'        // Duplicate
```

### File Operations

```php
'tabler:download'    // Download
'tabler:upload'      // Upload
'tabler:file'        // File
'tabler:folder'      // Folder
'heroicons:document' // Document
```

### Status Changes

```php
'heroicons:check-circle'  // Approve/Complete
'heroicons:x-circle'      // Reject/Cancel
'tabler:clock'            // Pending
'tabler:refresh'          // Processing
```

### Communication

```php
'heroicons:envelope'      // Email
'tabler:bell'             // Notification
'tabler:message'          // Message
'tabler:share'            // Share
```

### Navigation

```php
'tabler:chevron-right'    // Next/Forward
'tabler:chevron-left'     // Back/Previous
'tabler:arrow-up'         // Up
'tabler:arrow-down'       // Down
```

## Next Steps

- [Advanced Customization](./06-advanced.md)
- [Practical Examples](./07-examples.md)
- [API Reference](./08-api-reference.md)
