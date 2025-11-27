<?php

namespace AppMaker\InfoLists\Entries;

class TextEntry extends Entry
{
    protected bool $badge = false;
    protected array $colors = [];
    protected bool $copyable = false;

    public function badge(bool $badge = true): static
    {
        $this->badge = $badge;

        return $this;
    }

    public function colors(array $colors): static
    {
        $this->colors = $colors;

        return $this;
    }

    public function copyable(bool $copyable = true): static
    {
        $this->copyable = $copyable;

        return $this;
    }

    public function getType(): string
    {
        return 'text';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'badge' => $this->badge,
            'colors' => $this->colors,
            'copyable' => $this->copyable,
        ]);
    }
}
