<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAttendant();
    }

    public function view(User $user, Client $client): bool
    {
        // Super admin e atendente veem todos
        if ($user->isSuperAdmin() || $user->isAttendant()) {
            return true;
        }

        // Cliente vê apenas o próprio perfil
        if ($user->isClient() && $user->client) {
            return $client->id === $user->client->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAttendant();
    }

    public function update(User $user, Client $client): bool
    {
        return $user->isSuperAdmin() || $user->isAttendant();
    }

    public function delete(User $user, Client $client): bool
    {
        // Não permitir deletar cliente anônimo
        if ($client->is_anonymous) {
            return false;
        }

        return $user->isSuperAdmin();
    }

    /**
     * Adicionar saldo ao cliente
     */
    public function addBalance(User $user, Client $client): bool
    {
        return $user->isSuperAdmin() || $user->isAttendant();
    }

    /**
     * Receber pagamento de caderneta
     */
    public function payTab(User $user, Client $client): bool
    {
        return $user->isSuperAdmin() || $user->isAttendant();
    }
}
