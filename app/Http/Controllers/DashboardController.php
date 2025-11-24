<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Client;
use App\Models\SalePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with comprehensive statistics.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewDashboard');

        // Cache key for dashboard stats (cache for 5 minutes)
        $cacheKey = 'dashboard_stats_' . now()->format('Y-m-d_H-i');

        $data = Cache::remember($cacheKey, 300, fn () => [
            'todayStats' => $this->getTodayStats(),
            'clientStats' => $this->getClientStats(),
            'paymentStats' => $this->getPaymentStats(),
            'recentSales' => $this->getRecentSales(),
            'topClients' => $this->getTopClients(),
        ]);

        return Inertia::render('Dashboard', $data);
    }

    /**
     * Get today's sales statistics.
     */
    private function getTodayStats(): array
    {
        $today = now()->format('Y-m-d');

        $todaySales = Sale::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->get();

        $count = $todaySales->count();
        $total = $todaySales->sum('total_amount');
        $average = $count > 0 ? $total / $count : 0;

        // Calculate cash total (dinheiro em caixa)
        $cashTotal = SalePayment::whereHas('sale', function ($query) use ($today) {
            $query->whereDate('created_at', $today)
                  ->where('status', 'completed');
        })
        ->whereHas('paymentMethod', function ($query) {
            $query->where('code', 'cash');
        })
        ->sum('amount');

        return [
            'count' => $count,
            'total' => number_format($total, 2, '.', ''),
            'average' => number_format($average, 2, '.', ''),
            'cash' => number_format($cashTotal, 2, '.', ''),
        ];
    }

    /**
     * Get client statistics.
     */
    private function getClientStats(): array
    {
        $total = Client::where('is_anonymous', false)->count();

        // Clientes ativos (com pelo menos uma compra nos últimos 30 dias)
        $active = Client::where('is_anonymous', false)
            ->whereHas('sales', function ($query) {
                $query->where('created_at', '>=', now()->subDays(30))
                      ->where('status', 'completed');
            })
            ->count();

        // Clientes com débito na caderneta
        $withDebt = Client::where('is_anonymous', false)
            ->whereHas('balance', fn ($query) => $query->where('balance', '>', 0))
            ->count();

        return [
            'total' => $total,
            'active' => $active,
            'with_debt' => $withDebt,
        ];
    }

    /**
     * Get payment method statistics for today.
     */
    private function getPaymentStats(): array
    {
        $today = now()->format('Y-m-d');

        $paymentStats = DB::table('sale_payments')
            ->join('payment_methods', 'sale_payments.payment_method_id', '=', 'payment_methods.id')
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->whereDate('sales.created_at', $today)
            ->where('sales.status', 'completed')
            ->select(
                'payment_methods.name',
                DB::raw('SUM(sale_payments.amount) as total')
            )
            ->groupBy('payment_methods.id', 'payment_methods.name')
            ->orderBy('total', 'desc')
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name,
                'total' => number_format($item->total, 2, '.', ''),
            ])
            ->toArray();

        return $paymentStats;
    }

    /**
     * Get recent sales (last 10).
     */
    private function getRecentSales(): array
    {
        return Sale::with([
            'client:id,name,email',
            'user:id,name',
            'payments.paymentMethod:id,name'
        ])
        ->select('id', 'sale_number', 'client_id', 'user_id', 'total_amount', 'status', 'created_at')
        ->latest()
        ->take(10)
        ->get()
        ->map(fn ($sale) => [
            'id' => $sale->id,
            'sale_number' => $sale->sale_number,
            'client' => $sale->client ? [
                'id' => $sale->client->id,
                'name' => $sale->client->name,
            ] : ['name' => 'Anônimo'],
            'user' => $sale->user ? [
                'id' => $sale->user->id,
                'name' => $sale->user->name,
            ] : null,
            'total' => number_format($sale->total_amount, 2, '.', ''),
            'status' => $sale->status,
            'created_at' => $sale->created_at->toISOString(),
        ])
        ->toArray();
    }

    /**
     * Get top 5 clients by total spent.
     */
    private function getTopClients(): array
    {
        return Client::where('is_anonymous', false)
            ->withSum(['sales' => function ($query) {
                $query->where('status', 'completed');
            }], 'total_amount')
            ->get()
            ->filter(fn ($client) => ($client->sales_sum_total_amount ?? 0) > 0)
            ->sortByDesc('sales_sum_total_amount')
            ->take(5)
            ->map(fn ($client) => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'total_spent' => number_format($client->sales_sum_total_amount ?? 0, 2, '.', ''),
            ])
            ->values()
            ->toArray();
    }
}
