<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleLookupController;
use App\Models\Sale;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $stockCount = Vehicle::where('status', 'stock')->count();
        $soldThisMonth = Sale::whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->count();
        $profitThisMonth = Sale::query()
            ->whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->with('vehicle')
            ->get()
            ->sum(fn ($sale) => $sale->vehicle->margin() ?? 0);

        return view('dashboard', compact('stockCount', 'soldThisMonth', 'profitThisMonth'));
    })->name('dashboard');

    Route::resource('vehicles', VehicleController::class);

    Route::prefix('vehicles/lookup')->name('vehicles.lookup.')->group(function () {
        Route::get('status', [VehicleLookupController::class, 'status'])->name('status');
        Route::post('description', [VehicleLookupController::class, 'fromDescription'])->name('description');
        Route::post('logbook', [VehicleLookupController::class, 'fromLogbookImage'])->name('logbook');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
