<?php

namespace AppMaker\Tables\Concerns;

trait HasColumns
{
    protected array $columns = [];

    public function columns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    public function getColumns(): array
    {
        return array_map(fn ($column) => $column->toArray(), $this->columns);
    }

    public function getColumnByName(string $name): ?object
    {
        foreach ($this->columns as $column) {
            if ($column->getName() === $name) {
                return $column;
            }
        }

        return null;
    }
}
