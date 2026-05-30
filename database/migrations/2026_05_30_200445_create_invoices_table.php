<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 20)->unique()->comment('YYYY-NNNN');
            $table->foreignId('sale_id')->unique()->constrained('sales')->cascadeOnDelete();
            $table->date('issue_date');
            $table->enum('vat_scheme', ['margin', 'standard'])->default('margin')->comment('IE Margin Scheme for used cars');
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('total_gross', 10, 2);
            $table->string('pdf_path', 200)->nullable();
            $table->timestamps();

            $table->index('issue_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
