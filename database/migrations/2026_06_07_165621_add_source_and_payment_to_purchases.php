<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Skąd auto przyjechało (kategoria)
            // uk_auction = aukcja UK (Copart/Manheim/IAA)
            // uk_dealer = dealer UK
            // ie_private = prywatny z Irlandii
            // ie_dealer = dealer/komis IE
            // ie_auction = aukcja IE (DoneDeal, Carzone)
            // eu_import = import z UE (DE/NL/BE)
            // other = inne
            $table->string('source', 30)->nullable()->after('contractor_id');

            // Konkretne źródło tekstem (np. "Copart Birmingham" lub "Pan Tomek z Cork")
            $table->string('source_detail', 200)->nullable()->after('source');

            // Rozbicie płatności: gotówka vs przelew bankowy
            $table->decimal('paid_cash', 10, 2)->default(0)->after('purchase_price');
            $table->decimal('paid_bank', 10, 2)->default(0)->after('paid_cash');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['source', 'source_detail', 'paid_cash', 'paid_bank']);
        });
    }
};
