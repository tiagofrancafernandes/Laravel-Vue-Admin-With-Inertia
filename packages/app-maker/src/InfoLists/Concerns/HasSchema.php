<?php

namespace AppMaker\InfoLists\Concerns;

trait HasSchema
{
    protected array $schema = [];

    public function schema(array $schema): static
    {
        $this->schema = $schema;

        return $this;
    }

    public function getSchema(): array
    {
        return array_map(fn ($entry) => $entry->toArray(), $this->schema);
    }
}
