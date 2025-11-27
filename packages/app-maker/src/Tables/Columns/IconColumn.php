<?php

namespace AppMaker\Tables\Columns;

class IconColumn extends Column
{
    protected bool $boolean = false;
    protected array $trueIcon = ['name' => 'check-circle', 'color' => 'green'];
    protected array $falseIcon = ['name' => 'x-circle', 'color' => 'red'];
    protected ?string $icon = null;
    protected ?string $color = null;

    public function boolean(bool $boolean = true): static
    {
        $this->boolean = $boolean;

        return $this;
    }

    public function trueIcon(string $icon, string $color = 'green'): static
    {
        $this->trueIcon = ['name' => $icon, 'color' => $color];

        return $this;
    }

    public function falseIcon(string $icon, string $color = 'red'): static
    {
        $this->falseIcon = ['name' => $icon, 'color' => $color];

        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function color(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getType(): string
    {
        return 'icon';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'boolean' => $this->boolean,
            'trueIcon' => $this->trueIcon,
            'falseIcon' => $this->falseIcon,
            'icon' => $this->icon,
            'color' => $this->color,
        ]);
    }
}
