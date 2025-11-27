<?php

namespace AppMaker\Tables\Columns;

class ImageColumn extends Column
{
    protected ?string $disk = 'public';
    protected bool $rounded = false;
    protected ?int $width = 50;
    protected ?int $height = 50;

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function rounded(bool $rounded = true): static
    {
        $this->rounded = $rounded;

        return $this;
    }

    public function width(int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function height(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getType(): string
    {
        return 'image';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'disk' => $this->disk,
            'rounded' => $this->rounded,
            'width' => $this->width,
            'height' => $this->height,
        ]);
    }
}
