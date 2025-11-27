<?php

namespace AppMaker\Forms\Components;

class FileUpload extends Component
{
    protected string $disk = 'public';
    protected string $directory = 'uploads';
    protected array $acceptedFileTypes = [];
    protected ?int $maxSize = null;
    protected bool $image = false;
    protected bool $multiple = false;

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function directory(string $directory): static
    {
        $this->directory = $directory;

        return $this;
    }

    public function acceptedFileTypes(array $types): static
    {
        $this->acceptedFileTypes = $types;

        return $this;
    }

    public function maxSize(int $sizeInKB): static
    {
        $this->maxSize = $sizeInKB;
        $this->validationRules[] = "max:{$sizeInKB}";

        return $this;
    }

    public function image(): static
    {
        $this->image = true;
        $this->acceptedFileTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $this->validationRules[] = 'image';

        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function getType(): string
    {
        return 'file-upload';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'disk' => $this->disk,
            'directory' => $this->directory,
            'acceptedFileTypes' => $this->acceptedFileTypes,
            'maxSize' => $this->maxSize,
            'image' => $this->image,
            'multiple' => $this->multiple,
        ]);
    }
}
