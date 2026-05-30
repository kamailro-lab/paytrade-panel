<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['supplier', 'customer', 'both'])->default('customer');
            $table->string('name', 120);
            $table->string('phone', 30)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('address', 200)->nullable();
            $table->string('vat_number', 30)->nullable()->comment('IE VAT number');
            $table->string('ppsn', 12)->nullable()->comment('Personal Public Service Number IE');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('name');
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contractors');
    }
};
