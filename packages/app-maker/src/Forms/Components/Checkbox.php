<?php

namespace AppMaker\Forms\Components;

class Checkbox extends Component
{
    public function getType(): string
    {
        return 'checkbox';
    }

    public function toArray(): array
    {
        return parent::toArray();
    }
}
