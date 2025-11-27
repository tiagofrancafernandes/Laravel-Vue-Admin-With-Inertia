<?php

namespace AppMaker\Tables\Concerns;

trait HasFilters
{
    protected array $filters = [];

    public function filters(array $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function getFilters(): array
    {
        return array_map(fn ($filter) => $filter->toArray(), $this->filters);
    }

    public function getFilterByName(string $name): ?object
    {
        foreach ($this->filters as $filter) {
            if ($filter->getName() === $name) {
                return $filter;
            }
        }

        return null;
    }
}
