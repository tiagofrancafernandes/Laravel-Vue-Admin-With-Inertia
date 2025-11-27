<?php

namespace AppMaker\InfoLists\Entries;

use Illuminate\Contracts\Support\Arrayable;

abstract class Entry implements Arrayable
{
    protected string $name;
    protected ?string $label = null;
    protected ?\Closure $formatStateUsing = null;
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

    public function formatStateUsing(\Closure $callback): static
    {
        $this->formatStateUsing = $callback;

        return $this;
    }

    public function columnSpan(int $span): static
    {
        $this->columnSpan = $span;

        return $this;
    }

    public function dateTime(string $format = 'Y-m-d H:i:s'): static
    {
        $this->formatStateUsing(fn ($state) => $state?->format($format));

        return $this;
    }

    public function date(string $format = 'Y-m-d'): static
    {
        $this->formatStateUsing(fn ($state) => $state?->format($format));

        return $this;
    }

    abstract public function getType(): string;

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'name' => $this->name,
            'label' => $this->label,
            'columnSpan' => $this->columnSpan,
        ];
    }
}
