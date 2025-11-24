<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitClientProofRequest;
use App\Models\Client;
use App\Models\ClientProof;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ClientPortalController extends Controller
{
    /**
     * Show the client portal dashboard.
     */
    public function dashboard(): Response
    {
        $user = auth()->user();
        $client = Client::findOrFail($user->id);
        $client->load(['balance', 'sales']);

        $currentBalance = (float) ($client->balance?->balance ?? 0);
        $creditLimit = (float) ($client->balance?->credit_limit ?? 0);

        // Get recent transactions
        $recentTransactions = $client->ledger()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($tx) => [
                'id' => $tx->id,
                'type' => $tx->type,
                'amount' => $tx->amount,
                'balance_after' => $tx->balance_after,
                'tab_after' => $tx->tab_after,
                'description' => $tx->description,
                'created_at' => $tx->created_at->format('d/m/Y H:i'),
            ]);

        // Get recent sales
        $recentSales = $client->sales()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($sale) => [
                'id' => $sale->id,
                'code' => $sale->code,
                'total_amount' => $sale->total_amount,
                'status' => $sale->status,
                'created_at' => $sale->created_at->format('d/m/Y H:i'),
            ]);

        $stats = [
            'balance' => $currentBalance,
            'credit_limit' => $creditLimit,
            'total_due' => max(0, $creditLimit),
            'total_spent' => (float) $client->sales()->where('status', 'completed')->sum('total_amount'),
            'pending_proofs' => $client->proofs()->pending()->count(),
        ];

        return Inertia::render('ClientPortal/Dashboard', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
            ],
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
            'recentSales' => $recentSales,
        ]);
    }

    /**
     * Show the client's transaction statement.
     */
    public function statement(): Response
    {
        $user = auth()->user();
        $client = Client::findOrFail($user->id);
        $client->load('balance');

        // Get all ledger entries paginated
        $transactions = $client->ledger()
            ->latest()
            ->paginate(20)
            ->through(fn ($tx) => [
                'id' => $tx->id,
                'type' => $tx->type,
                'amount' => $tx->amount,
                'balance_after' => $tx->balance_after,
                'tab_after' => $tx->tab_after,
                'description' => $tx->description,
                'created_at' => $tx->created_at->format('d/m/Y H:i'),
            ]);

        return Inertia::render('ClientPortal/Statement', [
            'client' => [
                'name' => $client->name,
                'balance' => $client->balance?->balance ?? 0,
                'credit_limit' => $client->balance?->credit_limit ?? 0,
            ],
            'transactions' => $transactions,
        ]);
    }

    /**
     * Show the proof submission form.
     */
    public function proofSubmitForm(): Response
    {
        $user = auth()->user();

        // Get client's recent sales for reference
        $recentSales = Client::findOrFail($user->id)
            ->sales()
            ->where('status', 'completed')
            ->latest()
            ->limit(10)
            ->select('id', 'code', 'total_amount')
            ->get()
            ->map(fn ($sale) => [
                'id' => $sale->id,
                'code' => $sale->code,
                'display' => "{$sale->code} - R$ " . number_format($sale->total_amount, 2, ',', '.'),
            ]);

        return Inertia::render('ClientPortal/SubmitProof', [
            'recentSales' => $recentSales,
        ]);
    }

    /**
     * Store a new client proof submission.
     */
    public function submitProof(SubmitClientProofRequest $request)
    {
        $user = auth()->user();
        $validated = $request->validated();

        try {
            // Store file in secure location
            $filePath = $request->file('file')->store(
                "client-proofs/{$user->id}",
                'private'
            );

            // Create proof record
            $proof = ClientProof::create([
                'client_id' => $user->id,
                'sale_id' => $validated['sale_id'] ?? null,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'file_path' => $filePath,
                'status' => 'pending',
                'notes' => $validated['description'] ?? null,
            ]);

            return back()->with('success', 'Comprovante enviado com sucesso! Aguardando análise da administração.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao enviar comprovante: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the client's proof history.
     */
    public function proofHistory(): Response
    {
        $user = auth()->user();
        $client = Client::findOrFail($user->id);

        // Get all proofs with pagination
        $proofs = $client->proofs()
            ->with('admin:id,name')
            ->latest()
            ->paginate(10)
            ->through(fn ($proof) => [
                'id' => $proof->id,
                'type' => $proof->type,
                'amount' => $proof->amount,
                'status' => $proof->status,
                'created_at' => $proof->created_at->format('d/m/Y H:i'),
                'admin_notes' => $proof->notes,
                'admin_name' => $proof->admin?->name,
                'file_available' => Storage::disk('private')->exists($proof->file_path),
            ]);

        return Inertia::render('ClientPortal/ProofHistory', [
            'proofs' => $proofs,
        ]);
    }

    /**
     * Download a proof file.
     */
    public function downloadProof(ClientProof $proof)
    {
        $this->authorize('view', $proof);

        // Verify user can only download their own proofs
        if ($proof->client_id !== auth()->user()->id) {
            abort(403, 'Não autorizado');
        }

        if (!Storage::disk('private')->exists($proof->file_path)) {
            abort(404, 'Arquivo não encontrado');
        }

        return Storage::disk('private')->download($proof->file_path);
    }
}
