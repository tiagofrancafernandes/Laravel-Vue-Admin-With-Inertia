<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use App\Models\ClientBalance;
use App\Services\BalanceService;
use Illuminate\Http\Request;
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
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Client::class);

        $clients = Client::with('balance:client_id,balance_amount,tab_amount')
            ->where('is_anonymous', false)
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('document', 'like', "%{$search}%");
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
                    'balance_amount' => 0,
                    'tab_amount' => 0,
                ]);

                return $client;
            });

            // Invalidate cache when a new client is created
            Cache::forget('clients_list_active');

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
    public function selectList(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        $search = $request->input('search', '');

        $clients = Client::with('balance:client_id,balance_amount,tab_amount')
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
            'balance_amount' => $balance?->balance_amount ?? 0,
            'tab_amount' => $balance?->tab_amount ?? 0,
            'updated_at' => $balance?->updated_at,
        ]);
    }

    /**
     * Add balance to client.
     */
    public function addBalance(Request $request, Client $client)
    {
        $this->authorize('addBalance', $client);

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

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
    public function payTab(Request $request, Client $client)
    {
        $this->authorize('payTab', $client);

        $balance = $client->balance()->first();

        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:' . ($balance?->tab_amount ?? 0),
            ],
            'description' => 'nullable|string|max:500',
        ]);

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
}
