<?php

namespace App\Http\Controllers;

use App\Models\ClientProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminProofController extends Controller
{
    /**
     * Display a listing of all proofs with optional filters.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ClientProof::class);

        $status = $request->input('status');
        $type = $request->input('type');
        $search = $request->input('search');

        $query = ClientProof::with(['client:id,name,email', 'admin:id,name'])
            ->latest();

        // Filter by status
        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        // Filter by type
        if ($type && in_array($type, ['deposit', 'payment'])) {
            $query->where('type', $type);
        }

        // Search by client name or email
        if ($search) {
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        $proofs = $query->paginate(20)->through(fn ($proof) => [
            'id' => $proof->id,
            'type' => $proof->type,
            'amount' => $proof->amount,
            'status' => $proof->status,
            'client' => [
                'id' => $proof->client->id,
                'name' => $proof->client->name,
                'email' => $proof->client->email,
            ],
            'admin' => $proof->admin ? [
                'id' => $proof->admin->id,
                'name' => $proof->admin->name,
            ] : null,
            'created_at' => $proof->created_at->format('d/m/Y H:i'),
            'updated_at' => $proof->updated_at->format('d/m/Y H:i'),
        ]);

        return Inertia::render('Admin/ProofsList', [
            'proofs' => $proofs,
            'filters' => [
                'status' => $status,
                'type' => $type,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show details of a specific proof for review.
     */
    public function show(ClientProof $proof): Response
    {
        $this->authorize('view', $proof);

        $proof->load(['client:id,name,email,phone', 'admin:id,name', 'sale:id,code,total_amount']);

        $proofData = [
            'id' => $proof->id,
            'type' => $proof->type,
            'amount' => $proof->amount,
            'status' => $proof->status,
            'file_path' => $proof->file_path,
            'notes' => $proof->notes,
            'created_at' => $proof->created_at->format('d/m/Y H:i'),
            'updated_at' => $proof->updated_at->format('d/m/Y H:i'),
            'client' => [
                'id' => $proof->client->id,
                'name' => $proof->client->name,
                'email' => $proof->client->email,
                'phone' => $proof->client->phone,
            ],
            'admin' => $proof->admin ? [
                'id' => $proof->admin->id,
                'name' => $proof->admin->name,
            ] : null,
            'sale' => $proof->sale ? [
                'id' => $proof->sale->id,
                'code' => $proof->sale->code,
                'total_amount' => $proof->sale->total_amount,
            ] : null,
            'file_available' => Storage::disk('private')->exists($proof->file_path),
        ];

        return Inertia::render('Admin/ProofDetail', [
            'proof' => $proofData,
        ]);
    }

    /**
     * Approve a proof and update client balance if deposit.
     */
    public function approve(ClientProof $proof, Request $request)
    {
        $this->authorize('approve', $proof);

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $proof->update([
            'status' => 'approved',
            'notes' => $request->input('notes'),
            'admin_id' => auth()->id(),
        ]);

        // If proof is a deposit, add balance to client
        if ($proof->type === 'deposit') {
            $currentBalance = $proof->client->balance?->balance ?? 0;
            $proof->client->balance()->increment('balance', $proof->amount);

            // Log the transaction in ledger
            $proof->client->ledger()->create([
                'user_id' => auth()->id(),
                'type' => 'credit',
                'amount' => $proof->amount,
                'balance_before' => $currentBalance,
                'balance_after' => $currentBalance + $proof->amount,
                'description' => "Depósito aprovado - Comprovante #{$proof->id}",
            ]);
        }

        return back()->with('success', 'Comprovante aprovado com sucesso!');
    }

    /**
     * Reject a proof.
     */
    public function reject(ClientProof $proof, Request $request)
    {
        $this->authorize('reject', $proof);

        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $proof->update([
            'status' => 'rejected',
            'notes' => $request->input('notes'),
            'admin_id' => auth()->id(),
        ]);

        return back()->with('success', 'Comprovante rejeitado com sucesso!');
    }

    /**
     * Download proof file.
     */
    public function download(ClientProof $proof)
    {
        $this->authorize('view', $proof);

        if (!Storage::disk('private')->exists($proof->file_path)) {
            abort(404, 'Arquivo não encontrado');
        }

        return Storage::disk('private')->download($proof->file_path);
    }
}
