<?php

namespace AppMaker\Tables\Filters;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;

abstract class Filter implements Arrayable
{
    protected string $name;
    protected ?string $label = null;
    protected ?\Closure $query = null;
    protected mixed $default = null;
    protected bool|\Closure $visible = true;

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

    public function query(\Closure $callback): static
    {
        $this->query = $callback;

        return $this;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;

        return $this;
    }

    public function visible(bool|\Closure $condition = true): static
    {
        $this->visible = $condition;

        return $this;
    }

    public function apply(Builder $query, mixed $value): void
    {
        if ($this->query) {
            ($this->query)($query, $value);
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isVisible(): bool
    {
        return is_callable($this->visible) ? ($this->visible)() : $this->visible;
    }

    abstract public function getType(): string;

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'name' => $this->name,
            'label' => $this->label,
            'default' => $this->default,
            'visible' => $this->isVisible(),
        ];
    }
}
