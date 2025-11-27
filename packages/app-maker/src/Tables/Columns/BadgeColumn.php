<?php

namespace AppMaker\Tables\Columns;

class BadgeColumn extends Column
{
    protected array $colors = [];

    public function colors(array $colors): static
    {
        $this->colors = $colors;

        return $this;
    }

    public function getType(): string
    {
        return 'badge';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'colors' => $this->colors,
        ]);
    }
}
