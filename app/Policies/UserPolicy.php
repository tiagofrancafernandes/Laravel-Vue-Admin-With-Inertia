<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, User $model): bool
    {
        // Super admin não pode editar a si mesmo (segurança)
        if ($user->id === $model->id) {
            return false;
        }

        return $user->isSuperAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        // Não pode deletar a si mesmo
        if ($user->id === $model->id) {
            return false;
        }

        return $user->isSuperAdmin();
    }
}
