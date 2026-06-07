<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Cena docelowa sprzedaży (ustalona przy kupnie, nieobowiązkowa)
            // Odpowiednik kolumny "PRICE" z Google Sheets Stock
            $table->decimal('target_price', 10, 2)->nullable()->after('mileage_unit');
        });

        // Zmień purchases.contractor_id na nullable (żeby można było dodać szybki zakup bez dostawcy)
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['contractor_id']);
            $table->foreignId('contractor_id')->nullable()->change();
            $table->foreign('contractor_id')->references('id')->on('contractors')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('target_price');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['contractor_id']);
            $table->foreignId('contractor_id')->nullable(false)->change();
            $table->foreign('contractor_id')->references('id')->on('contractors')->restrictOnDelete();
        });
    }
};
