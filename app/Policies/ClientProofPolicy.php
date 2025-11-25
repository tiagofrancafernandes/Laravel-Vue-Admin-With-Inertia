<?php

namespace App\Policies;

use App\Models\ClientProof;
use App\Models\User;

class ClientProofPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ClientProof $clientProof): bool
    {
        // Client users can view their own proofs, admins can view any proof
        if ($user->isClient()) {
            return $user->id === $clientProof->client_id;
        }

        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only client type users can submit proofs
        return $user->isClient();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ClientProof $clientProof): bool
    {
        // Client users can only update their own pending proofs
        if ($user->isClient()) {
            return $user->id === $clientProof->client_id && $clientProof->status === 'pending';
        }

        // Admins can update any proof to change status
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ClientProof $clientProof): bool
    {
        // Client users can only delete their own pending proofs
        if ($user->isClient()) {
            return $user->id === $clientProof->client_id && $clientProof->status === 'pending';
        }

        // Only admins can delete proofs
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ClientProof $clientProof): bool
    {
        // Only admins can restore proofs
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ClientProof $clientProof): bool
    {
        // Only admins can permanently delete proofs
        return $user->isSuperAdmin();
    }

    /**
     * Determine if the user can approve a proof.
     */
    public function approve(User $user, ClientProof $clientProof): bool
    {
        // Only admins can approve proofs
        return $user->isSuperAdmin();
    }

    /**
     * Determine if the user can reject a proof.
     */
    public function reject(User $user, ClientProof $clientProof): bool
    {
        // Only admins can reject proofs
        return $user->isSuperAdmin();
    }
}
