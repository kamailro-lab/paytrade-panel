<?php

use App\Http\Controllers\ContractorController;
use App\Http\Controllers\CostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
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
    Route::resource('contractors', ContractorController::class);

    Route::post('vehicles/{vehicle}/purchase', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::delete('vehicles/{vehicle}/purchase', [PurchaseController::class, 'destroy'])->name('purchases.destroy');
    Route::post('vehicles/{vehicle}/sale', [SaleController::class, 'store'])->name('sales.store');
    Route::delete('vehicles/{vehicle}/sale', [SaleController::class, 'destroy'])->name('sales.destroy');
    Route::post('vehicles/{vehicle}/costs', [CostController::class, 'store'])->name('costs.store');
    Route::delete('vehicles/{vehicle}/costs/{cost}', [CostController::class, 'destroy'])->name('costs.destroy');

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
