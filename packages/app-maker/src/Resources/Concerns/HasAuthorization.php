<?php

namespace AppMaker\Resources\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasAuthorization
{
    public function authorize(string $action, ?Model $record = null): bool
    {
        if (!config('appmaker.authorization_enabled', true)) {
            return true;
        }

        $permission = $this->getPermissionName($action);

        // Check Spatie permission
        if (!auth()->user()?->can($permission)) {
            return false;
        }

        // Optional: Check policy if exists
        $policyClass = $this->getPolicyClass();

        if ($policyClass && class_exists($policyClass)) {
            return auth()->user()->can($action, $record ?? $this->getModel());
        }

        return true;
    }

    protected function getPermissionName(string $action): string
    {
        $pattern = config('appmaker.permission_pattern', '{action}-{resource}');

        return str_replace(
            ['{action}', '{resource}'],
            [$action, $this->getUri()],
            $pattern
        );
    }

    protected function getPolicyClass(): ?string
    {
        $modelClass = $this->getModel();

        if (!$modelClass) {
            return null;
        }

        $policyClass = str_replace('Models', 'Policies', $modelClass) . 'Policy';

        return class_exists($policyClass) ? $policyClass : null;
    }
}
