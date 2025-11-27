# Advanced Customization

This guide covers advanced topics for extending and customizing AppMaker.

## Custom Column Types

Create your own column types by extending the base `Column` class.

### Creating a Custom Column

```php
<?php

namespace App\AppMaker\Columns;

use AppMaker\Tables\Columns\Column;

class ProgressColumn extends Column
{
    protected string $type = 'progress';
    protected int $max = 100;
    protected string $color = 'blue';

    public function max(int $max): static
    {
        $this->max = $max;
        return $this;
    }

    public function color(string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'max' => $this->max,
            'color' => $this->color,
        ]);
    }
}
```

### Using the Custom Column

```php
use App\AppMaker\Columns\ProgressColumn;

public function table(): Table
{
    return Table::make()
        ->columns([
            ProgressColumn::make('completion')
                ->max(100)
                ->color('green'),
        ]);
}
```

### Creating the Vue Component

Create `resources/js/Components/AppMaker/Table/Columns/ProgressColumn.vue`:

```vue
<script setup>
const props = defineProps({
    value: {
        type: Number,
        required: true,
    },
    column: {
        type: Object,
        required: true,
    },
});

const percentage = computed(() => {
    return Math.min(100, (props.value / props.column.max) * 100);
});

const colorClasses = computed(() => {
    const colors = {
        blue: 'bg-blue-600',
        green: 'bg-green-600',
        yellow: 'bg-yellow-600',
        red: 'bg-red-600',
    };
    return colors[props.column.color] || colors.blue;
});
</script>

<template>
    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
        <div
            :class="[colorClasses, 'h-2 rounded-full transition-all']"
            :style="{ width: percentage + '%' }"
        ></div>
    </div>
</template>
```

Register the component in your TableColumn.vue:

```vue
<script setup>
import ProgressColumn from './Columns/ProgressColumn.vue';

const component = computed(() => {
    const componentMap = {
        progress: ProgressColumn,
        // ... other types
    };
    return componentMap[props.column.type];
});
</script>
```

## Custom Form Components

Extend AppMaker with your own form components.

### Creating a Custom Component

```php
<?php

namespace App\AppMaker\Components;

use AppMaker\Forms\Components\Component;

class ColorPicker extends Component
{
    protected string $type = 'color-picker';
    protected array $presets = [];
    protected bool $showAlpha = false;

    public function presets(array $colors): static
    {
        $this->presets = $colors;
        return $this;
    }

    public function withAlpha(bool $show = true): static
    {
        $this->showAlpha = $show;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'presets' => $this->presets,
            'showAlpha' => $this->showAlpha,
        ]);
    }
}
```

### Using the Custom Component

```php
use App\AppMaker\Components\ColorPicker;

public function form(): Form
{
    return Form::make()
        ->schema([
            ColorPicker::make('brand_color')
                ->label('Brand Color')
                ->presets(['#3B82F6', '#10B981', '#F59E0B'])
                ->withAlpha()
                ->required(),
        ]);
}
```

### Creating the Vue Component

Create `resources/js/Components/AppMaker/Form/Fields/ColorPicker.vue`:

```vue
<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: String,
    field: {
        type: Object,
        required: true,
    },
    error: String,
});

const emit = defineEmits(['update:modelValue']);

const localValue = ref(props.modelValue || '#000000');

const updateValue = (value) => {
    localValue.value = value;
    emit('update:modelValue', value);
};
</script>

<template>
    <div>
        <label class="block text-sm font-medium mb-2">
            {{ field.label }}
        </label>

        <div class="flex items-center gap-4">
            <input
                type="color"
                :value="localValue"
                @input="updateValue($event.target.value)"
                class="h-10 w-20 rounded border border-gray-300"
            />

            <input
                type="text"
                :value="localValue"
                @input="updateValue($event.target.value)"
                class="flex-1 rounded-md border-gray-300"
                :placeholder="field.placeholder"
            />
        </div>

        <div v-if="field.presets?.length" class="mt-2 flex gap-2">
            <button
                v-for="color in field.presets"
                :key="color"
                type="button"
                @click="updateValue(color)"
                :style="{ backgroundColor: color }"
                class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-500"
            />
        </div>

        <p v-if="error" class="mt-1 text-sm text-red-600">
            {{ error }}
        </p>
    </div>
</template>
```

## Custom Vue Components

Replace default AppMaker components with your own.

### Overriding the Table Component

Create your custom table in `resources/js/Components/CustomTable.vue`:

```vue
<script setup>
import { ref } from 'vue';

const props = defineProps({
    table: Object,
    records: Object,
});

const emit = defineEmits(['search', 'filter', 'sort', 'page']);

// Your custom implementation
</script>

<template>
    <div class="custom-table">
        <!-- Your custom table markup -->
    </div>
</template>
```

Use it in your resource page:

```vue
<script setup>
import CustomTable from '@/Components/CustomTable.vue';
</script>

<template>
    <CustomTable
        :table="table"
        :records="records"
        @search="handleSearch"
    />
</template>
```

## Performance Optimization

### Eager Loading Relationships

AppMaker automatically detects and eager loads relationships, but you can optimize further:

```php
public function table(): Table
{
    return Table::make()
        ->columns([
            TextColumn::make('author.name'),
            TextColumn::make('category.name'),
            TextColumn::make('tags.count')
                ->formatStateUsing(fn ($state, $record) => $record->tags->count()),
        ])
        ->modifyQueryUsing(function ($query) {
            // Manually optimize complex relationships
            $query->with(['author', 'category', 'tags']);
        });
}
```

### Pagination Optimization

Limit the number of records per page for large datasets:

```php
public function table(): Table
{
    return Table::make()
        ->defaultPerPage(10)
        ->perPageOptions([10, 25, 50]);
}
```

### Caching

Cache expensive computations:

```php
SelectFilter::make('category_id')
    ->options(function () {
        return Cache::remember('categories_options', 3600, function () {
            return Category::pluck('name', 'id');
        });
    })
```

## Extending ResourceBase

Add custom methods to all resources by extending ResourceBase.

### Creating a Base Resource

```php
<?php

namespace App\AppMaker\Resources;

use AppMaker\Resources\ResourceBase as BaseResourceBase;

abstract class ResourceBase extends BaseResourceBase
{
    /**
     * Get the resource icon
     */
    public function getIcon(): string
    {
        return 'tabler:file';
    }

    /**
     * Get the resource color
     */
    public function getColor(): string
    {
        return 'blue';
    }

    /**
     * Enable soft deletes by default
     */
    public function softDeletes(): bool
    {
        return true;
    }

    /**
     * Get the model's display name attribute
     */
    public function getRecordTitle($record): string
    {
        return $record->name ?? $record->title ?? "#{$record->id}";
    }
}
```

Use your base class for all resources:

```php
<?php

namespace App\AppMaker\Resources;

use App\AppMaker\Resources\ResourceBase;

class PostResource extends ResourceBase
{
    protected ?string $model = Post::class;

    public function getIcon(): string
    {
        return 'tabler:article';
    }

    // ...
}
```

## Custom Action Handlers

Create reusable action logic.

### Creating an Action Trait

```php
<?php

namespace App\AppMaker\Actions\Concerns;

trait HasNotification
{
    protected ?string $notificationTitle = null;
    protected ?string $notificationMessage = null;

    public function notify(string $title, string $message): static
    {
        $this->notificationTitle = $title;
        $this->notificationMessage = $message;
        return $this;
    }

    protected function sendNotification(): void
    {
        if ($this->notificationTitle) {
            // Send notification using your preferred method
            session()->flash('success', $this->notificationMessage);
        }
    }
}
```

Use the trait in actions:

```php
use App\AppMaker\Actions\Concerns\HasNotification;
use AppMaker\Actions\Action;

Action::make('publish')
    ->notify('Published', 'Post has been published successfully')
    ->action(function ($record) {
        $record->publish();
        $this->sendNotification();
    })
```

## Global Configuration

Customize AppMaker behavior globally in `config/appmaker.php`:

```php
return [
    'resources' => [
        // Auto-register resources
        App\AppMaker\Resources\PostResource::class,
        App\AppMaker\Resources\ProductResource::class,
    ],

    'middleware' => ['web', 'auth', 'verified'],

    'authorization_enabled' => true,

    'permission_pattern' => '{action}-{resource}',

    // Default pagination
    'default_per_page' => 15,
    'per_page_options' => [10, 15, 25, 50, 100],

    // Default table settings
    'table' => [
        'striped' => true,
        'hoverable' => true,
    ],

    // Icon set preferences
    'icons' => [
        'default_set' => 'tabler',
        'fallback' => 'tabler:question-mark',
    ],

    // File upload defaults
    'uploads' => [
        'disk' => 'public',
        'max_size' => 10240, // 10MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf'],
    ],
];
```

## Hooks and Lifecycle Events

Add hooks to resource lifecycle events.

### Before Save Hook

```php
public function form(): Form
{
    return Form::make()
        ->schema([
            TextInput::make('slug')
                ->required()
                ->rules(['unique:posts,slug']),
        ])
        ->beforeSave(function ($data, $record) {
            // Auto-generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }
            return $data;
        });
}
```

### After Save Hook

```php
public function form(): Form
{
    return Form::make()
        ->schema([/* ... */])
        ->afterSave(function ($record, $data) {
            // Clear cache
            Cache::forget("post_{$record->id}");

            // Trigger event
            event(new PostSaved($record));
        });
}
```

## Multi-Tenancy

Implement multi-tenancy by filtering queries.

### Tenant-Aware Resource

```php
<?php

namespace App\AppMaker\Resources;

use AppMaker\Resources\ResourceBase;

abstract class TenantResource extends ResourceBase
{
    public function table(): Table
    {
        return Table::make()
            ->modifyQueryUsing(function ($query) {
                return $query->where('tenant_id', auth()->user()->tenant_id);
            })
            ->columns($this->getColumns());
    }

    abstract protected function getColumns(): array;
}
```

Use in your resources:

```php
class PostResource extends TenantResource
{
    protected ?string $model = Post::class;

    protected function getColumns(): array
    {
        return [
            TextColumn::make('title'),
            TextColumn::make('author.name'),
        ];
    }
}
```

## Custom Validation

Add complex validation logic.

### Custom Validation Rules

```php
use AppMaker\Forms\Components\TextInput;

TextInput::make('username')
    ->rules([
        'required',
        'min:3',
        'max:20',
        'regex:/^[a-zA-Z0-9_]+$/',
        function ($attribute, $value, $fail) {
            if (in_array($value, ['admin', 'root', 'system'])) {
                $fail('The username is reserved.');
            }
        },
    ])
```

### Conditional Validation

```php
TextInput::make('vat_number')
    ->rules(function ($get) {
        if ($get('is_company')) {
            return ['required', 'regex:/^\d{9}$/'];
        }
        return [];
    })
```

## Theming

Customize the AppMaker appearance.

### Custom Colors

Create a custom theme in your Tailwind config:

```javascript
// tailwind.config.js
export default {
    theme: {
        extend: {
            colors: {
                'appmaker': {
                    50: '#f0f9ff',
                    100: '#e0f2fe',
                    // ... your custom colors
                    900: '#0c4a6e',
                },
            },
        },
    },
}
```

Use in components:

```php
Action::make('custom')
    ->color('appmaker')
    ->icon('tabler:star')
```

## Next Steps

- [Practical Examples](./07-examples.md)
- [API Reference](./08-api-reference.md)
- [Icons Guide](./09-icons.md)
