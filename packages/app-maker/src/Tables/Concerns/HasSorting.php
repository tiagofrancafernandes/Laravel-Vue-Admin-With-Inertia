<?php

namespace AppMaker\Tables\Concerns;

trait HasSorting
{
    protected ?string $defaultSortColumn = null;
    protected string $defaultSortDirection = 'asc';

    public function defaultSort(string $column, string $direction = 'asc'): static
    {
        $this->defaultSortColumn = $column;
        $this->defaultSortDirection = $direction;

        return $this;
    }

    public function getSortingConfig(): array
    {
        return [
            'default_column' => $this->defaultSortColumn,
            'default_direction' => $this->defaultSortDirection,
        ];
    }
}
