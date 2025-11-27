<?php

namespace AppMaker\Forms\Components;

class TextInput extends Component
{
    protected ?int $maxLength = null;
    protected ?int $minLength = null;
    protected string $inputType = 'text';

    public function maxLength(int $length): static
    {
        $this->maxLength = $length;
        $this->validationRules[] = "max:{$length}";

        return $this;
    }

    public function minLength(int $length): static
    {
        $this->minLength = $length;
        $this->validationRules[] = "min:{$length}";

        return $this;
    }

    public function email(): static
    {
        $this->inputType = 'email';
        $this->validationRules[] = 'email';

        return $this;
    }

    public function password(): static
    {
        $this->inputType = 'password';

        return $this;
    }

    public function url(): static
    {
        $this->inputType = 'url';
        $this->validationRules[] = 'url';

        return $this;
    }

    public function tel(): static
    {
        $this->inputType = 'tel';

        return $this;
    }

    public function numeric(): static
    {
        $this->inputType = 'number';
        $this->validationRules[] = 'numeric';

        return $this;
    }

    public function getType(): string
    {
        return 'text-input';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'maxLength' => $this->maxLength,
            'minLength' => $this->minLength,
            'inputType' => $this->inputType,
        ]);
    }
}
