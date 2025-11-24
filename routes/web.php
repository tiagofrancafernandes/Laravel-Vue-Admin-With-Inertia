<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome', [
    'canLogin' => Route::has('login'),
    'canRegister' => Route::has('register'),
    'laravelVersion' => Application::VERSION,
    'phpVersion' => PHP_VERSION,
]));

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Sales routes
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');

    // Clients routes
    Route::resource('clients', ClientController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('clients/{client}/add-balance', [ClientController::class, 'addBalance'])->name('clients.add-balance');
    Route::post('clients/{client}/pay-tab', [ClientController::class, 'payTab'])->name('clients.pay-tab');
});

// Client Portal Routes
Route::middleware(['auth', 'verified', 'client_portal'])->prefix('client-portal')->group(function () {
    Route::get('/', [ClientPortalController::class, 'dashboard'])->name('client.portal.dashboard');
    Route::get('/statement', [ClientPortalController::class, 'statement'])->name('client.portal.statement');
    Route::get('/proof/submit', [ClientPortalController::class, 'proofSubmitForm'])->name('client.portal.proof.form');
    Route::post('/proof', [ClientPortalController::class, 'submitProof'])->name('client.portal.proof.store');
    Route::get('/proofs', [ClientPortalController::class, 'proofHistory'])->name('client.portal.proofs');
    Route::get('/proof/{proof}/download', [ClientPortalController::class, 'downloadProof'])->name('client.portal.proof.download');
});

// API routes
Route::middleware('auth')->group(function () {
    Route::get('/api/clients/select', [ClientController::class, 'selectList'])->name('api.clients.select');
    Route::get('/api/clients/{client}/balance', [ClientController::class, 'balance'])->name('api.clients.balance');
});

require __DIR__ . '/auth.php';
