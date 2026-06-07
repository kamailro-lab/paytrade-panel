<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Gwarancja w miesiącach (0 = brak)
            $table->integer('warranty_months')->default(0)->after('payment_method');

            // Depozyt / zaliczka osobno od gotówki
            $table->decimal('deposit', 10, 2)->default(0)->after('sale_price');

            // Rozbicie płatności: gotówka + bank (depozyt już osobno wyżej)
            $table->decimal('paid_cash', 10, 2)->default(0)->after('deposit');
            $table->decimal('paid_bank', 10, 2)->default(0)->after('paid_cash');
        });

        // Zmień sale_date na nullable (dla quick sale bez konkretnej daty)
        // + contractor_id na nullable (klient opcjonalny przy szybkim dodawaniu)
        Schema::table('sales', function (Blueprint $table) {
            $table->date('sale_date')->nullable()->change();
            $table->dropForeign(['contractor_id']);
            $table->foreignId('contractor_id')->nullable()->change();
            $table->foreign('contractor_id')->references('id')->on('contractors')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['warranty_months', 'deposit', 'paid_cash', 'paid_bank']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->date('sale_date')->nullable(false)->change();
            $table->dropForeign(['contractor_id']);
            $table->foreignId('contractor_id')->nullable(false)->change();
            $table->foreign('contractor_id')->references('id')->on('contractors')->restrictOnDelete();
        });
    }
};
