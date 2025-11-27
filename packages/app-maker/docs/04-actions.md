# Actions

Actions allow you to add custom functionality to your resources, from simple record updates to complex workflows.

## Types of Actions

AppMaker supports two types of actions:

1. **Record Actions** - Operate on individual records
2. **Bulk Actions** - Operate on multiple selected records

## Record Actions

Record actions appear on each row in the table.

### Basic Action

```php
use AppMaker\Actions\Action;

Table::make()
    ->recordActions([
        Action::make('approve')
            ->action(function ($record) {
                $record->update(['status' => 'approved']);
            }),
    ])
```

### Action Configuration

#### Label and Icon

```php
Action::make('publish')
    ->label('Publish Now')
    ->icon('check')
    ->color('green')
```

Available colors: `gray`, `red`, `yellow`, `green`, `blue`

#### Action Logic

```php
Action::make('send_notification')
    ->action(function ($record) {
        Mail::to($record->user)->send(new PostPublished($record));
        
        $record->update([
            'notification_sent_at' => now(),
        ]);
    })
```

#### Confirmation Dialog

Add a confirmation step:

```php
Action::make('delete_permanently')
    ->requiresConfirmation(
        true,
        'Delete Permanently?',
        'This action cannot be undone. All data will be permanently deleted.'
    )
    ->action(fn ($record) => $record->forceDelete())
```

#### Conditional Visibility

Show/hide actions based on record state:

```php
Action::make('publish')
    ->visible(fn ($record) => $record->status === 'draft')
    ->action(fn ($record) => $record->publish())

Action::make('unpublish')
    ->hidden(fn ($record) => $record->status !== 'published')
    ->action(fn ($record) => $record->unpublish())
```

#### Authorization

Restrict actions based on permissions:

```php
Action::make('approve')
    ->can('approve-posts')
    ->action(fn ($record) => $record->approve())
```

## Bulk Actions

Bulk actions operate on multiple selected records.

### Basic Bulk Action

```php
use AppMaker\Actions\BulkAction;
use AppMaker\Actions\BulkActionGroup;

Table::make()
    ->bulkActions([
        BulkActionGroup::make([
            BulkAction::make('publish')
                ->action(function ($records) {
                    $records->each->update(['status' => 'published']);
                }),
        ]),
    ])
```

### Pre-built Bulk Actions

#### Delete Bulk Action

```php
use AppMaker\Actions\DeleteBulkAction;

BulkActionGroup::make([
    DeleteBulkAction::make(), // Soft deletes all selected
])
```

### Custom Bulk Actions

```php
BulkAction::make('archive')
    ->label('Archive Selected')
    ->icon('archive')
    ->color('yellow')
    ->requiresConfirmation(
        true,
        'Archive Records?',
        'Selected records will be archived and hidden from the main list.'
    )
    ->action(function ($records) {
        $records->each(function ($record) {
            $record->update(['archived_at' => now()]);
        });
    })
```

### Bulk Action with Feedback

```php
BulkAction::make('send_emails')
    ->action(function ($records) {
        $sent = 0;
        
        $records->each(function ($record) use (&$sent) {
            Mail::to($record->email)->send(new Newsletter());
            $sent++;
        });
        
        session()->flash('success', "Sent {$sent} emails successfully!");
    })
```

## Action Examples

### Workflow Actions

```php
// Approve/Reject workflow
Action::make('approve')
    ->color('green')
    ->action(fn ($record) => $record->approve())
    ->visible(fn ($record) => $record->isPending()),

Action::make('reject')
    ->color('red')
    ->requiresConfirmation()
    ->action(fn ($record) => $record->reject())
    ->visible(fn ($record) => $record->isPending()),
```

### Status Transitions

```php
Action::make('mark_as_completed')
    ->visible(fn ($record) => $record->status === 'in_progress')
    ->action(function ($record) {
        $record->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }),
```

### Feature Toggle

```php
Action::make('feature')
    ->label('Mark as Featured')
    ->icon('star')
    ->color('yellow')
    ->visible(fn ($record) => !$record->is_featured)
    ->action(fn ($record) => $record->update(['is_featured' => true])),

Action::make('unfeature')
    ->label('Remove from Featured')
    ->icon('star-outline')
    ->visible(fn ($record) => $record->is_featured)
    ->action(fn ($record) => $record->update(['is_featured' => false])),
```

### Send Notifications

```php
Action::make('notify')
    ->label('Send Notification')
    ->icon('mail')
    ->requiresConfirmation(
        true,
        'Send Notification?',
        'This will send an email to the user.'
    )
    ->action(function ($record) {
        Notification::send($record->user, new RecordUpdated($record));
        
        $record->update(['last_notified_at' => now()]);
    }),
```

### Export Data

```php
Action::make('export_pdf')
    ->label('Export as PDF')
    ->icon('download')
    ->action(function ($record) {
        return response()->download(
            $record->generatePdf(),
            "record-{$record->id}.pdf"
        );
    }),
```

### Clone Record

```php
Action::make('duplicate')
    ->label('Duplicate')
    ->icon('copy')
    ->action(function ($record) {
        $clone = $record->replicate();
        $clone->title = $record->title . ' (Copy)';
        $clone->save();
        
        session()->flash('success', 'Record duplicated successfully!');
    }),
```

## Complete Example

```php
public function table(): Table
{
    return Table::make()
        ->heading('Blog Posts')
        ->columns([...])
        ->recordActions([
            // Publish draft posts
            Action::make('publish')
                ->label('Publish')
                ->icon('check')
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

            // Feature/Unfeature posts
            Action::make('feature')
                ->label('Feature')
                ->icon('star')
                ->color('yellow')
                ->visible(fn ($record) => !$record->is_featured)
                ->action(fn ($record) => $record->update(['is_featured' => true])),

            Action::make('unfeature')
                ->label('Unfeature')
                ->icon('star-outline')
                ->visible(fn ($record) => $record->is_featured)
                ->action(fn ($record) => $record->update(['is_featured' => false])),

            // Send notification
            Action::make('notify_subscribers')
                ->label('Notify Subscribers')
                ->icon('mail')
                ->can('send-notifications')
                ->requiresConfirmation(
                    true,
                    'Notify All Subscribers?',
                    'This will send an email to all subscribers.'
                )
                ->action(function ($record) {
                    Notification::send(
                        User::whereHas('subscriptions')->get(),
                        new NewPostPublished($record)
                    );
                }),
        ])
        ->bulkActions([
            BulkActionGroup::make([
                // Publish multiple posts
                BulkAction::make('publish_all')
                    ->label('Publish Selected')
                    ->icon('check')
                    ->color('green')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $records->each(function ($record) {
                            $record->update([
                                'status' => 'published',
                                'published_at' => now(),
                            ]);
                        });
                    }),

                // Archive posts
                BulkAction::make('archive')
                    ->label('Archive Selected')
                    ->icon('archive')
                    ->color('yellow')
                    ->action(fn ($records) => 
                        $records->each->update(['status' => 'archived'])
                    ),

                // Delete posts
                DeleteBulkAction::make(),
            ]),
        ]);
}
```

## Best Practices

### 1. Use Descriptive Labels

```php
// Good
Action::make('approve')->label('Approve Request')

// Bad
Action::make('approve') // Uses default "Approve"
```

### 2. Add Confirmation for Destructive Actions

```php
Action::make('delete_permanently')
    ->requiresConfirmation(
        true,
        'Permanently Delete?',
        'This action cannot be undone.'
    )
```

### 3. Check Permissions

```php
Action::make('delete')
    ->can('delete-posts')
    ->action(...)
```

### 4. Provide User Feedback

```php
Action::make('export')
    ->action(function ($record) {
        $pdf = $record->generatePdf();
        
        session()->flash('success', 'PDF exported successfully!');
        
        return response()->download($pdf);
    })
```

### 5. Hide Irrelevant Actions

```php
// Show approve only for pending records
Action::make('approve')
    ->visible(fn ($record) => $record->isPending())
```

## Action Responses

Actions can return various responses:

### Redirect

```php
Action::make('view_details')
    ->action(fn ($record) => redirect()->route('posts.show', $record))
```

### Download

```php
Action::make('download')
    ->action(fn ($record) => response()->download($record->file_path))
```

### Flash Message

```php
Action::make('notify')
    ->action(function ($record) {
        // Send notification
        session()->flash('success', 'Notification sent!');
    })
```

## Next Steps

- [Learn about InfoLists](./05-infolists.md)
- [Advanced Customization](./06-advanced.md)
- [Practical Examples](./07-examples.md)
