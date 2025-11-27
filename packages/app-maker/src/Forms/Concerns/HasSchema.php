<?php

namespace AppMaker\Forms\Concerns;

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
        return array_map(fn ($component) => $component->toArray(), $this->schema);
    }

    public function getComponentByName(string $name): ?object
    {
        foreach ($this->schema as $component) {
            if ($component->getName() === $name) {
                return $component;
            }
        }

        return null;
    }
}
