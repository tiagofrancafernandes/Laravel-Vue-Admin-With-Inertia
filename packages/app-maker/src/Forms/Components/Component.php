<?php

namespace AppMaker\Forms\Components;

use Illuminate\Contracts\Support\Arrayable;

abstract class Component implements Arrayable
{
    protected string $name;
    protected ?string $label = null;
    protected mixed $default = null;
    protected bool $required = false;
    protected ?string $helperText = null;
    protected ?string $placeholder = null;
    protected bool|\Closure $disabled = false;
    protected bool|\Closure $visible = true;
    protected int $columnSpan = 1;
    protected array $validationRules = [];

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = str($name)->headline()->toString();
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;

        return $this;
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;

        if ($required && !in_array('required', $this->validationRules)) {
            $this->validationRules[] = 'required';
        }

        return $this;
    }

    public function helperText(string $text): static
    {
        $this->helperText = $text;

        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function disabled(bool|\Closure $disabled = true): static
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function visible(bool|\Closure $visible = true): static
    {
        $this->visible = $visible;

        return $this;
    }

    public function columnSpan(int $span): static
    {
        $this->columnSpan = $span;

        return $this;
    }

    public function rules(array|string $rules): static
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        $this->validationRules = array_merge($this->validationRules, $rules);

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValidationRules(): array|string
    {
        return $this->validationRules;
    }

    public function isVisible(): bool
    {
        return is_callable($this->visible) ? ($this->visible)() : $this->visible;
    }

    public function isDisabled(): bool
    {
        return is_callable($this->disabled) ? ($this->disabled)() : $this->disabled;
    }

    abstract public function getType(): string;

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'name' => $this->name,
            'label' => $this->label,
            'default' => $this->default,
            'required' => $this->required,
            'helperText' => $this->helperText,
            'placeholder' => $this->placeholder,
            'disabled' => $this->isDisabled(),
            'visible' => $this->isVisible(),
            'columnSpan' => $this->columnSpan,
        ];
    }
}
