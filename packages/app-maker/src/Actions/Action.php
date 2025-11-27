<?php

namespace AppMaker\Actions;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;

class Action implements Arrayable
{
    protected string $name;
    protected ?string $label = null;
    protected ?\Closure $action = null;
    protected bool|\Closure $visible = true;
    protected bool|\Closure $hidden = false;
    protected bool $requiresConfirmation = false;
    protected ?string $confirmationTitle = null;
    protected ?string $confirmationText = null;
    protected ?string $icon = null;
    protected ?string $color = null;
    protected ?string $permission = null;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = str($name)->headline()->toString();
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function action(\Closure $callback): static
    {
        $this->action = $callback;

        return $this;
    }

    public function visible(bool|\Closure $condition): static
    {
        $this->visible = $condition;

        return $this;
    }

    public function hidden(bool|\Closure $condition): static
    {
        $this->hidden = $condition;

        return $this;
    }

    public function requiresConfirmation(
        bool $requires = true,
        ?string $title = null,
        ?string $text = null
    ): static {
        $this->requiresConfirmation = $requires;
        $this->confirmationTitle = $title ?? 'Are you sure?';
        $this->confirmationText = $text ?? 'This action cannot be undone.';

        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function color(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function can(string $permission): static
    {
        $this->permission = $permission;

        return $this;
    }

    public function execute(Model $record): mixed
    {
        if ($this->action) {
            return ($this->action)($record);
        }

        return null;
    }

    public function isVisible(Model $record): bool
    {
        if (is_callable($this->visible)) {
            return ($this->visible)($record);
        }

        if (is_callable($this->hidden)) {
            return !($this->hidden)($record);
        }

        return $this->visible && !$this->hidden;
    }

    public function isAuthorized(): bool
    {
        if ($this->permission === null) {
            return true;
        }

        return auth()->user()?->can($this->permission) ?? false;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'requiresConfirmation' => $this->requiresConfirmation,
            'confirmationTitle' => $this->confirmationTitle,
            'confirmationText' => $this->confirmationText,
            'icon' => $this->icon,
            'color' => $this->color,
            'authorized' => $this->isAuthorized(),
        ];
    }
}
