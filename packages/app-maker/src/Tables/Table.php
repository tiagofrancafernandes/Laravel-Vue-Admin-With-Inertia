<?php

namespace AppMaker\Tables;

use AppMaker\Tables\Concerns\HasActions;
use AppMaker\Tables\Concerns\HasColumns;
use AppMaker\Tables\Concerns\HasFilters;
use AppMaker\Tables\Concerns\HasPagination;
use AppMaker\Tables\Concerns\HasSearch;
use AppMaker\Tables\Concerns\HasSorting;
use Illuminate\Contracts\Support\Arrayable;

class Table implements Arrayable
{
    use HasActions;
    use HasColumns;
    use HasFilters;
    use HasPagination;
    use HasSearch;
    use HasSorting;

    protected ?string $heading = null;
    protected bool $striped = true;
    protected array $crudUrls = [];
    protected bool $selectable = true;

    public static function make(): static
    {
        return new static();
    }

    public function heading(string $heading): static
    {
        $this->heading = $heading;

        return $this;
    }

    public function striped(bool $striped = true): static
    {
        $this->striped = $striped;

        return $this;
    }

    public function crudUrls(array $urls): static
    {
        $this->crudUrls = $urls;

        return $this;
    }

    public function selectable(bool $selectable = true): static
    {
        $this->selectable = $selectable;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'heading' => $this->heading,
            'striped' => $this->striped,
            'selectable' => $this->selectable,
            'columns' => $this->getColumns(),
            'filters' => $this->getFilters(),
            'actions' => $this->getActions(),
            'pagination' => $this->getPaginationConfig(),
            'search' => $this->getSearchConfig(),
            'sorting' => $this->getSortingConfig(),
            'crudUrls' => $this->crudUrls,
        ];
    }
}
