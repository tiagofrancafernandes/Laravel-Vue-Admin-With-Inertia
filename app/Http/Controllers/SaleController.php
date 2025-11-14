<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Sale;
use App\Models\Client;
use App\Models\PaymentMethod;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SaleController extends Controller
{
    public function __construct(protected SaleService $saleService) {}

    /**
     * Display a listing of sales.
     */
    public function index(Request $request): Response
    {
        $sales = Sale::with(['client', 'user', 'payments'])
            ->when($request->search, function ($query, $search) {
                $query->where('sale_number', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(15);

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new sale.
     */
    public function create(): Response
    {
        return Inertia::render('Sales/Create', [
            'paymentMethods' => PaymentMethod::where('is_active', true)
                ->orderBy('display_order')
                ->get(),
            'anonymousClientId' => Client::where('name', 'Anônimo')->value('id'),
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
        return Inertia::render('Sales/Show', [
            'sale' => $sale->load(['client', 'user', 'payments.paymentMethod']),
        ]);
    }

    /**
     * Cancel a sale.
     */
    public function cancel(Sale $sale)
    {
        try {
            $this->saleService->cancelSale($sale);
            return back()->with('success', 'Venda cancelada com sucesso!');
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
