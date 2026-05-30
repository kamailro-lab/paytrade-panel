<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('order_source', 60)->nullable()->after('status')
                ->comment('PRV, autofind, popular, lukasinski, CARSTOCK, paulek, tonycash, etc.');
            $table->string('done_deal', 6)->nullable()->after('order_source')
                ->comment('Y / N / hub / YNP');
            $table->string('www_listed', 6)->nullable()->after('done_deal')
                ->comment('Y / N / hub / YNP — czy wystawione w internecie');
            $table->boolean('motortrans')->default(false)->after('www_listed');
            $table->string('mileage_unit', 5)->default('km')->after('mileage_km')
                ->comment('km lub mil');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['order_source', 'done_deal', 'www_listed', 'motortrans', 'mileage_unit']);
        });
    }
};
