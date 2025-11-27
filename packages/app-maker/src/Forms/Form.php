<?php

namespace AppMaker\Forms;

use AppMaker\Forms\Concerns\HasSchema;
use AppMaker\Forms\Concerns\HasValidation;
use Illuminate\Contracts\Support\Arrayable;

class Form implements Arrayable
{
    use HasSchema;
    use HasValidation;

    protected ?string $heading = null;
    protected array $columns = [1];
    protected ?string $submitLabel = 'Save';
    protected ?string $cancelLabel = 'Cancel';

    public static function make(): static
    {
        return new static();
    }

    public function heading(string $heading): static
    {
        $this->heading = $heading;

        return $this;
    }

    public function columns(int|array $columns): static
    {
        $this->columns = is_int($columns) ? [$columns] : $columns;

        return $this;
    }

    public function submitLabel(string $label): static
    {
        $this->submitLabel = $label;

        return $this;
    }

    public function cancelLabel(string $label): static
    {
        $this->cancelLabel = $label;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'heading' => $this->heading,
            'schema' => $this->getSchema(),
            'validation' => $this->getValidationRules(),
            'columns' => $this->columns,
            'submitLabel' => $this->submitLabel,
            'cancelLabel' => $this->cancelLabel,
        ];
    }
}
