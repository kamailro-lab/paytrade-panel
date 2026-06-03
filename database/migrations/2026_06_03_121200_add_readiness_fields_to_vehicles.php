<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->boolean('service_done')->default(false)->after('nct_expiry');
            $table->date('service_date')->nullable()->after('service_done');
            $table->boolean('timing_belt_checked')->default(false)->after('service_date');
            $table->date('timing_belt_date')->nullable()->after('timing_belt_checked');
            $table->boolean('nct_passed')->default(false)->after('nct_expiry');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'service_done', 'service_date',
                'timing_belt_checked', 'timing_belt_date',
                'nct_passed',
            ]);
        });
    }
};
