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
        $client = Client::with(['ledger'])->first();

        // Get all ledger entries paginated
        $transactions = $client?->ledger()
            ->orderBy('created_at', 'desc')
            ->paginate(20) ?? [];

        return Inertia::render('ClientPortal/Statement', [
            'transactions' => $transactions,
        ]);
    }

    /**
     * Show the proof submission form.
     */
    public function showProofForm(): Response
    {
        return Inertia::render('ClientPortal/SubmitProof');
    }

    /**
     * Store a new client proof submission.
     */
    public function submitProof(SubmitClientProofRequest $request)
    {
        $validated = $request->validated();

        // Store file
        $filePath = $request->file('file')->store(
            'client-proofs',
            'private'
        );

        // Create proof record
        $proof = ClientProof::create([
            'client_id' => auth()->user()->client->id ?? null,
            'sale_id' => $validated['sale_id'] ?? null,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'file_path' => $filePath,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comprovante enviado com sucesso! Aguarde a análise do administrador.',
            'proof' => $proof,
        ]);
    }

    /**
     * Show the client's proof history.
     */
    public function proofHistory(): Response
    {
        $user = auth()->user();
        $client = Client::with(['proofs' => function ($query) {
            $query->orderBy('created_at', 'desc')->paginate(20);
        }])->first();

        return Inertia::render('ClientPortal/ProofHistory', [
            'proofs' => $client?->proofs ?? [],
        ]);
    }

    /**
     * Download a proof file.
     */
    public function downloadProof(ClientProof $proof)
    {
        $this->authorize('view', $proof);

        if (!Storage::disk('private')->exists($proof->file_path)) {
            return response()->json([
                'error' => 'Arquivo não encontrado',
            ], 404);
        }

        return Storage::disk('private')->download($proof->file_path);
    }
}
