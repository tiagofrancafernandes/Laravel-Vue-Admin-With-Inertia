<?php

namespace AppMaker\Forms\Components;

class DatePicker extends Component
{
    protected string $format = 'Y-m-d';
    protected ?string $minDate = null;
    protected ?string $maxDate = null;
    protected bool $withTime = false;

    public function format(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function minDate(string|\DateTime $date): static
    {
        $this->minDate = $date instanceof \DateTime ? $date->format('Y-m-d') : $date;

        return $this;
    }

    public function maxDate(string|\DateTime $date): static
    {
        $this->maxDate = $date instanceof \DateTime ? $date->format('Y-m-d') : $date;

        return $this;
    }

    public function withTime(bool $withTime = true): static
    {
        $this->withTime = $withTime;

        return $this;
    }

    public function getType(): string
    {
        return 'date-picker';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'format' => $this->format,
            'minDate' => $this->minDate,
            'maxDate' => $this->maxDate,
            'withTime' => $this->withTime,
        ]);
    }
}
