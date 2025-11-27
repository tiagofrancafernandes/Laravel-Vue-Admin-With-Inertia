<?php

namespace AppMaker\Actions;

use Illuminate\Contracts\Support\Arrayable;

class BulkActionGroup implements Arrayable
{
    protected array $actions;

    public function __construct(array $actions)
    {
        $this->actions = $actions;
    }

    public static function make(array $actions): static
    {
        return new static($actions);
    }

    public function toArray(): array
    {
        return array_map(fn ($action) => $action->toArray(), $this->actions);
    }
}
