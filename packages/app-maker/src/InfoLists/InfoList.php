<?php

namespace AppMaker\InfoLists;

use AppMaker\InfoLists\Concerns\HasSchema;
use Illuminate\Contracts\Support\Arrayable;

class InfoList implements Arrayable
{
    use HasSchema;

    protected int $columns = 1;

    public static function make(): static
    {
        return new static();
    }

    public function columns(int $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'schema' => $this->getSchema(),
            'columns' => $this->columns,
        ];
    }
}
