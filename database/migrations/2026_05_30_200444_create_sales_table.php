<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->unique()->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('contractor_id')->constrained('contractors')->restrictOnDelete();
            $table->date('sale_date');
            $table->decimal('sale_price', 10, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'card', 'financing', 'other'])->default('bank_transfer');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('sale_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
