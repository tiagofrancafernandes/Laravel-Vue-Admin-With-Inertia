<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use App\Models\ClientBalance;
use App\Models\ClientLedger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $clients = Client::when($request->search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(15);

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Clients/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request)
    {
        try {
            $client = Client::create($request->validated());

            // Create initial balance if provided
            if ($request->input('initial_balance', 0) > 0) {
                ClientBalance::create([
                    'client_id' => $client->id,
                    'balance' => $request->input('initial_balance'),
                    'credit_limit' => 0,
                ]);

                // Record in ledger
                ClientLedger::create([
                    'client_id' => $client->id,
                    'user_id' => auth()->id(),
                    'type' => 'credit',
                    'amount' => $request->input('initial_balance'),
                    'balance_before' => 0,
                    'balance_after' => $request->input('initial_balance'),
                    'description' => 'Saldo inicial',
                ]);
            } else {
                // Create zero balance
                ClientBalance::create([
                    'client_id' => $client->id,
                    'balance' => 0,
                    'credit_limit' => 0,
                ]);
            }

            return redirect()->route('clients.show', $client)->with('success', 'Cliente criado com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client): Response
    {
        return Inertia::render('Clients/Show', [
            'client' => $client,
            'balance' => $client->balance,
            'recentSales' => $client->sales()
                ->with('payments')
                ->latest()
                ->take(10)
                ->get()
                ->map(fn ($sale) => [
                    'id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'total' => $sale->total,
                    'status' => $sale->status,
                    'created_at' => $sale->created_at,
                ]),
            'recentTransactions' => $client->ledger()
                ->latest()
                ->take(20)
                ->get()
                ->map(fn ($transaction) => [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'balance_before' => $transaction->balance_before,
                    'balance_after' => $transaction->balance_after,
                    'description' => $transaction->description,
                    'created_at' => $transaction->created_at,
                ]),
            'stats' => [
                'sales_count' => $client->sales()->count(),
                'total_spent' => $client->sales()->where('status', 'completed')->sum('total'),
                'last_sale' => $client->sales()->latest()->first()?->only('created_at'),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return back()->withErrors(['error' => 'Clientes não podem ser editados']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return back()->withErrors(['error' => 'Clientes não podem ser editados']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return back()->withErrors(['error' => 'Clientes não podem ser deletados']);
    }

    /**
     * Get clients list for select/autocomplete.
     */
    public function selectList(Request $request)
    {
        $search = $request->input('search', '');

        $clients = Client::when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        })
            ->select('id', 'name', 'email', 'phone')
            ->limit(10)
            ->get();

        return response()->json($clients);
    }
}
