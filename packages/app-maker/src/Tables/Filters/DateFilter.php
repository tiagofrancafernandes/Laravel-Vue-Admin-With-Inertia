<?php

namespace AppMaker\Tables\Filters;

class DateFilter extends Filter
{
    protected ?string $minDate = null;
    protected ?string $maxDate = null;
    protected string $format = 'Y-m-d';

    public function minDate(string $date): static
    {
        $this->minDate = $date;

        return $this;
    }

    public function maxDate(string $date): static
    {
        $this->maxDate = $date;

        return $this;
    }

    public function format(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function getType(): string
    {
        return 'date';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'minDate' => $this->minDate,
            'maxDate' => $this->maxDate,
            'format' => $this->format,
        ]);
    }
}
