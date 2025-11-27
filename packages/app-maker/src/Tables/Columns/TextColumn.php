<?php

namespace AppMaker\Tables\Columns;

class TextColumn extends Column
{
    protected ?int $limit = null;
    protected bool $wrap = false;
    protected ?string $copyable = null;

    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    public function wrap(bool $wrap = true): static
    {
        $this->wrap = $wrap;

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
            'limit' => $this->limit,
            'wrap' => $this->wrap,
            'copyable' => $this->copyable,
        ]);
    }
}
