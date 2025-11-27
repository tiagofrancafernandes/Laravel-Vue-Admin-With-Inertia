<?php

namespace AppMaker\Forms\Components;

class Textarea extends Component
{
    protected int $rows = 4;
    protected ?int $maxLength = null;

    public function rows(int $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    public function maxLength(int $length): static
    {
        $this->maxLength = $length;
        $this->validationRules[] = "max:{$length}";

        return $this;
    }

    public function getType(): string
    {
        return 'textarea';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'rows' => $this->rows,
            'maxLength' => $this->maxLength,
        ]);
    }
}
