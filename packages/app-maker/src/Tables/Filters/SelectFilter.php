<?php

namespace AppMaker\Tables\Filters;

class SelectFilter extends Filter
{
    protected array $options = [];
    protected bool $multiple = false;
    protected bool $searchable = false;

    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function getType(): string
    {
        return 'select';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'options' => $this->options,
            'multiple' => $this->multiple,
            'searchable' => $this->searchable,
        ]);
    }
}
