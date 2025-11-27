<?php

use AppMaker\Http\Controllers\ActionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AppMaker Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the AppMaker service provider.
| They provide the backend endpoints for AppMaker resources.
|
*/

// Action execution route
Route::middleware(config('appmaker.middleware', ['web', 'auth']))->group(function () {
    Route::post('appmaker/action/{resource}/{action}/{record}', [ActionController::class, 'handle'])
        ->name('appmaker.action');

    Route::post('appmaker/bulk-action/{resource}/{action}', [ActionController::class, 'handleBulk'])
        ->name('appmaker.bulk-action');
});
