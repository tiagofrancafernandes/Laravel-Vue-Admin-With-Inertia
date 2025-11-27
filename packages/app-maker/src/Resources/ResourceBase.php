<?php

namespace AppMaker\Resources;

use AppMaker\Forms\Form;
use AppMaker\InfoLists\InfoList;
use AppMaker\Resources\Concerns\HasAuthorization;
use AppMaker\Tables\Table;

abstract class ResourceBase
{
    use HasAuthorization;

    protected ?string $model = null;
    protected ?string $uri = null;
    protected ?string $resourceId = null;
    protected ?string $title = null;
    protected ?string $singularLabel = null;
    protected ?string $pluralLabel = null;

    abstract public function table(): Table;

    public function form(): ?Form
    {
        return null;
    }

    public function infoList(): ?InfoList
    {
        return null;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getResourceId(): string
    {
        return $this->resourceId ?? str($this->uri)->singular()->toString();
    }

    public function getTitle(): string
    {
        return $this->title ?? str($this->uri)->headline()->toString();
    }

    public function getSingularLabel(): string
    {
        return $this->singularLabel ?? str($this->uri)->singular()->headline()->toString();
    }

    public function getPluralLabel(): string
    {
        return $this->pluralLabel ?? str($this->uri)->plural()->headline()->toString();
    }
}
