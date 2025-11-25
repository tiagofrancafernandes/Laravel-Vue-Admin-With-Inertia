<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Requests\UpdateClientCreditLimitRequest;
use App\Http\Requests\AddClientBalanceRequest;
use App\Http\Requests\PayClientTabRequest;
use App\Http\Requests\ClientFilterRequest;
use App\Http\Requests\ClientSelectRequest;
use App\Models\Client;
use App\Models\ClientBalance;
use App\Services\BalanceService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function __construct(protected BalanceService $balanceService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ClientFilterRequest $request): Response
    {
        $this->authorize('viewAny', Client::class);

        $search = $request->input('search');

        $clients = Client::with('balance:client_id,balance,credit_limit')
            ->where('is_anonymous', false)
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('cpf_cnpj', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => $request->getFilters(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create', Client::class);

        return Inertia::render('Clients/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request)
    {
        try {
            $client = DB::transaction(function () use ($request) {
                $client = Client::create($request->validated());

                // Criar saldo inicial (sempre zero)
                ClientBalance::create([
                    'client_id' => $client->id,
                    'balance' => 0,
                    'credit_limit' => 0,
                ]);

                return $client;
            });

            // Invalidate cache when a new client is created
            Cache::forget('clients_list_active');

            // If request is AJAX/modal, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cliente criado com sucesso!',
                    'client' => [
                        'id' => $client->id,
                        'name' => $client->name,
                        'email' => $client->email,
                        'phone' => $client->phone,
                    ],
                ]);
            }

            return redirect()->route('clients.show', $client)->with('success', 'Cliente criado com sucesso!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client): Response
    {
        $this->authorize('view', $client);

        $client->load('balance');

        $recentSales = $client->sales()
            ->with('payments.paymentMethod')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($sale) => [
                'id' => $sale->id,
                'code' => $sale->code,
                'total_amount' => $sale->total_amount,
                'status' => $sale->status,
                'created_at' => $sale->created_at,
            ]);

        $recentTransactions = $client->ledger()
            ->latest()
            ->take(20)
            ->get()
            ->map(fn ($transaction) => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'balance_after' => $transaction->balance_after,
                'tab_after' => $transaction->tab_after,
                'description' => $transaction->description,
                'created_at' => $transaction->created_at,
            ]);

        $stats = [
            'sales_count' => $client->sales()->count(),
            'total_spent' => $client->sales()->where('status', 'completed')->sum('total_amount'),
            'last_sale' => $client->sales()->latest()->first()?->created_at,
        ];

        return Inertia::render('Clients/Show', [
            'client' => $client,
            'recentSales' => $recentSales,
            'recentTransactions' => $recentTransactions,
            'stats' => $stats,
        ]);
    }

    /**
     * Get clients list for select/autocomplete.
     */
    public function selectList(ClientSelectRequest $request)
    {
        $this->authorize('viewAny', Client::class);

        $search = $request->getSearch();

        $clients = Client::with('balance:client_id,balance,credit_limit')
            ->where('is_anonymous', false)
            ->when($search, function ($query, $search) {
                $query->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%");
            })
            ->select('id', 'name', 'email', 'phone')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => $clients,
        ]);
    }

    /**
     * Get client balance.
     */
    public function balance(Client $client)
    {
        $this->authorize('view', $client);

        $balance = $client->balance()->first();

        return response()->json([
            'client_id' => $client->id,
            'balance' => $balance?->balance ?? 0,
            'credit_limit' => $balance?->credit_limit ?? 0,
            'updated_at' => $balance?->updated_at,
        ]);
    }

    /**
     * Add balance to client.
     */
    public function addBalance(AddClientBalanceRequest $request, Client $client)
    {
        $this->authorize('addBalance', $client);

        try {
            $this->balanceService->addBalance(
                $client->id,
                $request->input('amount'),
                $request->input('description', 'Adição de saldo')
            );

            return back()->with('success', 'Saldo adicionado com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Pay client tab (caderneta).
     */
    public function payTab(PayClientTabRequest $request, Client $client)
    {
        $this->authorize('payTab', $client);

        try {
            $this->balanceService->payTab(
                $client->id,
                $request->input('amount'),
                $request->input('description', 'Pagamento de dívida')
            );

            return back()->with('success', 'Pagamento registrado com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the client.
     */
    public function edit(Client $client): Response
    {
        $this->authorize('update', $client);

        $client->load('balance');

        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    /**
     * Update the client information.
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        try {
            $client->update($request->validated());

            // Invalidate cache when a client is updated
            Cache::forget('clients_list_active');

            return redirect()->route('clients.show', $client)
                ->with('success', 'Cliente atualizado com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the client credit limit.
     */
    public function updateCreditLimit(UpdateClientCreditLimitRequest $request, Client $client)
    {
        $this->authorize('manageCreditLimit', $client);

        try {
            $creditLimit = (float) $request->input('credit_limit');

            DB::transaction(function () use ($client, $creditLimit) {
                $client->load('balance');

                if ($client->balance) {
                    $client->balance->update(['credit_limit' => $creditLimit]);
                } else {
                    ClientBalance::create([
                        'client_id' => $client->id,
                        'balance' => 0,
                        'credit_limit' => $creditLimit,
                    ]);
                }
            });

            // Invalidate cache when credit limit is updated
            Cache::forget('clients_list_active');

            return back()->with('success', 'Limite de crédito atualizado com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
