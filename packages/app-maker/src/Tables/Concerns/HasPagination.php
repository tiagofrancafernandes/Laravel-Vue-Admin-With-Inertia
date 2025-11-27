<?php

namespace AppMaker\Tables\Concerns;

trait HasPagination
{
    protected array|bool $paginationOptions = [10, 25, 50, 100];
    protected int $defaultPagination = 25;

    public function paginated(array|bool $options): static
    {
        $this->paginationOptions = $options;

        return $this;
    }

    public function defaultPaginationPageOption(int $default): static
    {
        $this->defaultPagination = $default;

        return $this;
    }

    public function getPaginationConfig(): array
    {
        return [
            'enabled' => $this->paginationOptions !== false,
            'options' => is_array($this->paginationOptions) ? $this->paginationOptions : [],
            'default' => $this->defaultPagination,
        ];
    }
}
