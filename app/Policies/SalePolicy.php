<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    /**
     * Visualizar qualquer venda
     */
    public function viewAny(User $user): bool
    {
        // Super admin e atendente veem todas
        // Cliente vê apenas as suas
        return $user->isSuperAdmin()
            || $user->isAttendant()
            || $user->isClient();
    }

    /**
     * Visualizar uma venda específica
     */
    public function view(User $user, Sale $sale): bool
    {
        // Super admin e atendente veem todas
        if ($user->isSuperAdmin() || $user->isAttendant()) {
            return true;
        }

        // Cliente vê apenas se for dele
        if ($user->isClient() && $user->client) {
            return $sale->client_id === $user->client->id;
        }

        return false;
    }

    /**
     * Criar venda
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAttendant();
    }

    /**
     * Cancelar venda
     */
    public function cancel(User $user, Sale $sale): bool
    {
        // Apenas super admin pode cancelar
        if (!$user->isSuperAdmin()) {
            return false;
        }

        // Não pode cancelar venda já cancelada
        if ($sale->status === 'cancelled') {
            return false;
        }

        return true;
    }

    /**
     * Excluir venda (soft delete)
     */
    public function delete(User $user, Sale $sale): bool
    {
        return $user->isSuperAdmin();
    }
}
