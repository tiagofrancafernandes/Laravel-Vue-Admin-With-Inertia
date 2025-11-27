<?php

namespace AppMaker\Forms\Components;

class Select extends Component
{
    protected array|\Closure $options = [];
    protected bool $multiple = false;
    protected bool $searchable = false;

    public function options(array|\Closure $options): static
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
        $options = is_callable($this->options) ? ($this->options)() : $this->options;

        return array_merge(parent::toArray(), [
            'options' => $options,
            'multiple' => $this->multiple,
            'searchable' => $this->searchable,
        ]);
    }
}
