<?php

namespace AppMaker\Tables\Columns;

use Illuminate\Contracts\Support\Arrayable;

abstract class Column implements Arrayable
{
    protected string $name;
    protected ?string $label = null;
    protected bool $sortable = false;
    protected bool $searchable = false;
    protected ?\Closure $formatStateUsing = null;
    protected bool|\Closure $hidden = false;
    protected int $columnSpan = 1;

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

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function formatStateUsing(\Closure $callback): static
    {
        $this->formatStateUsing = $callback;

        return $this;
    }

    public function hidden(bool|\Closure $condition = true): static
    {
        $this->hidden = $condition;

        return $this;
    }

    public function columnSpan(int $span): static
    {
        $this->columnSpan = $span;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isHidden(): bool
    {
        return is_callable($this->hidden) ? ($this->hidden)() : $this->hidden;
    }

    abstract public function getType(): string;

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'name' => $this->name,
            'label' => $this->label,
            'sortable' => $this->sortable,
            'searchable' => $this->searchable,
            'hidden' => $this->isHidden(),
            'columnSpan' => $this->columnSpan,
        ];
    }
}
