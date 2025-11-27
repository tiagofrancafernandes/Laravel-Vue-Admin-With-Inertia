<?php

namespace AppMaker\Tables\Concerns;

trait HasSearch
{
    protected bool $searchEnabled = true;
    protected array $searchableColumns = [];

    public function searchable(bool $enabled = true): static
    {
        $this->searchEnabled = $enabled;

        return $this;
    }

    public function getSearchConfig(): array
    {
        // Auto-detect searchable columns
        $searchableColumns = array_filter($this->columns, fn ($col) => $col->isSearchable());

        return [
            'enabled' => $this->searchEnabled && count($searchableColumns) > 0,
            'columns' => array_map(fn ($col) => $col->getName(), $searchableColumns),
        ];
    }
}
