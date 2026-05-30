<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('contractor_id')->constrained('contractors')->restrictOnDelete();
            $table->date('purchase_date');
            $table->decimal('purchase_price', 10, 2);
            $table->char('currency', 3)->default('EUR');
            $table->decimal('vrt_paid', 10, 2)->default(0);
            $table->decimal('transport_cost', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('purchase_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
