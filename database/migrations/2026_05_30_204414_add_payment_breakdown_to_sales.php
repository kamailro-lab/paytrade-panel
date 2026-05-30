<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('payment_credit', 10, 2)->default(0)->after('sale_price')
                ->comment('Kwota z kredytu');
            $table->decimal('payment_bank', 10, 2)->default(0)->after('payment_credit')
                ->comment('Przelew bankowy');
            $table->decimal('payment_cash_deposit', 10, 2)->default(0)->after('payment_bank')
                ->comment('Gotówka lub depozyt');
            $table->decimal('payment_trade', 10, 2)->default(0)->after('payment_cash_deposit')
                ->comment('Wartość auta w trade-in');
            $table->string('credit_contract_number', 30)->nullable()->after('payment_trade')
                ->comment('Numer umowy kredytowej / FFU / JRK / tradehub');
            $table->string('warranty', 40)->nullable()->after('credit_contract_number')
                ->comment('12 MX Car protect / 6 MX Car protect / no warranty / custom');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'payment_credit', 'payment_bank', 'payment_cash_deposit', 'payment_trade',
                'credit_contract_number', 'warranty',
            ]);
        });
    }
};
