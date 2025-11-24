<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\SaleFilterRequest;
use App\Models\Sale;
use App\Models\Client;
use App\Models\PaymentMethod;
use App\Services\SaleService;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class SaleController extends Controller
{
    public function __construct(protected SaleService $saleService)
    {
    }

    /**
     * Display a listing of sales.
     */
    public function index(SaleFilterRequest $request): Response
    {
        $this->authorize('viewAny', Sale::class);

        $query = Sale::with([
            'client:id,name,email',
            'user:id,name',
            'payments.paymentMethod:id,name',
        ])
            ->select('id', 'code', 'client_id', 'user_id', 'total_amount', 'status', 'created_at');

        // Cliente vê apenas suas vendas
        if ($request->user()->isClient() && $request->user()->client) {
            $query->where('client_id', $request->user()->client->id);
        }

        // Filtros
        $search = $request->input('search');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $status = $request->input('status');

        $query->when($search, function ($query, $search) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhereHas('client', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        });

        $query->when($dateFrom, fn ($q, $date) => $q->whereDate('created_at', '>=', $date));
        $query->when($dateTo, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
        $query->when($status, fn ($q, $status) => $q->where('status', $status));

        $sales = $query->latest()->paginate(15);

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'filters' => $request->getFilters(),
        ]);
    }

    /**
     * Show the form for creating a new sale.
     */
    public function create(): Response
    {
        $this->authorize('create', Sale::class);

        // Cache payment methods for 1 hour
        $paymentMethods = Cache::remember('payment_methods_active', 3600, fn () => PaymentMethod::where('is_active', true)
            ->select('id', 'name', 'code', 'description')
            ->orderBy('display_order')
            ->get());

        // Cache anonymous client for 24 hours
        $anonymousClientId = Cache::remember('anonymous_client_id', 86400, fn () => Client::where('is_anonymous', true)->value('id'));

        return Inertia::render('Sales/Create', [
            'paymentMethods' => $paymentMethods,
            'anonymousClientId' => $anonymousClientId,
        ]);
    }

    /**
     * Store a newly created sale in storage.
     */
    public function store(StoreSaleRequest $request)
    {
        try {
            $sale = $this->saleService->createSale($request->validated());

            return redirect()->route('sales.show', $sale)->with('success', 'Venda criada com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale): Response
    {
        $this->authorize('view', $sale);

        // Load relationships with specific columns to optimize query size
        $sale->load([
            'client:id,name,email,phone,cpf_cnpj',
            'user:id,name,email',
            'payments:id,sale_id,payment_method_id,amount,metadata',
            'payments.paymentMethod:id,name,code',
        ]);

        return Inertia::render('Sales/Show', [
            'sale' => $sale,
        ]);
    }

    /**
     * Cancel a sale.
     */
    public function cancel(Sale $sale)
    {
        $this->authorize('cancel', $sale);

        try {
            $this->saleService->cancelSale($sale);

            return redirect()->route('sales.index')->with('success', 'Venda cancelada com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show form not implemented (edit not allowed).
     */
    public function edit(string $id)
    {
        return back()->withErrors(['error' => 'Vendas não podem ser editadas']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return back()->withErrors(['error' => 'Use o botão de cancelamento']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return back()->withErrors(['error' => 'Vendas não podem ser editadas']);
    }
}
