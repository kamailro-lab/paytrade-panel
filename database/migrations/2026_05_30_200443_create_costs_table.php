<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->enum('category', ['repair', 'parts', 'advertising', 'cleaning', 'transport', 'other'])->default('other');
            $table->string('description', 200);
            $table->decimal('amount', 10, 2);
            $table->date('cost_date');
            $table->timestamps();

            $table->index('cost_date');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('costs');
    }
};
