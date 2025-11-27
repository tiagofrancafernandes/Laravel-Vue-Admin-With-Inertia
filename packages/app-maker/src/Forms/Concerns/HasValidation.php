<?php

namespace AppMaker\Forms\Concerns;

trait HasValidation
{
    protected array $customRules = [];

    public function rules(array $rules): static
    {
        $this->customRules = $rules;

        return $this;
    }

    public function getValidationRules(): array
    {
        $rules = [];

        foreach ($this->schema as $component) {
            $componentRules = $component->getValidationRules();

            if (!empty($componentRules)) {
                $rules[$component->getName()] = $componentRules;
            }
        }

        // Merge with custom rules
        return array_merge($rules, $this->customRules);
    }
}
