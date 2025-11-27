<?php

namespace AppMaker\Tables\Concerns;

trait HasActions
{
    protected array $recordActions = [];
    protected array $bulkActions = [];
    protected array $headerActions = [];

    public function recordActions(array $actions): static
    {
        $this->recordActions = $actions;

        return $this;
    }

    public function bulkActions(array $actions): static
    {
        $this->bulkActions = $actions;

        return $this;
    }

    public function headerActions(array $actions): static
    {
        $this->headerActions = $actions;

        return $this;
    }

    public function getActions(): array
    {
        return [
            'record' => array_map(fn ($action) => $action->toArray(), $this->recordActions),
            'bulk' => array_map(fn ($action) => $action->toArray(), $this->bulkActions),
            'header' => array_map(fn ($action) => $action->toArray(), $this->headerActions),
        ];
    }

    public function getRecordActionByName(string $name): ?object
    {
        foreach ($this->recordActions as $action) {
            if ($action->getName() === $name) {
                return $action;
            }
        }

        return null;
    }
}
