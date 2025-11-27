<?php

namespace AppMaker\Tables\Filters;

class BooleanFilter extends Filter
{
    protected string $trueLabel = 'Yes';
    protected string $falseLabel = 'No';

    public function trueLabel(string $label): static
    {
        $this->trueLabel = $label;

        return $this;
    }

    public function falseLabel(string $label): static
    {
        $this->falseLabel = $label;

        return $this;
    }

    public function getType(): string
    {
        return 'boolean';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'trueLabel' => $this->trueLabel,
            'falseLabel' => $this->falseLabel,
        ]);
    }
}
