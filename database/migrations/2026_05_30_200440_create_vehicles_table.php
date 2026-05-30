<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('registration', 12)->unique()->comment('IE format: YY-CC-NNNNN');
            $table->string('logbook_no', 20)->nullable()->comment('VRC / V5C');
            $table->string('make', 50);
            $table->string('model', 80);
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedSmallInteger('engine_cc')->nullable();
            $table->string('fuel', 20)->nullable();
            $table->string('color', 40)->nullable();
            $table->unsignedInteger('mileage_km')->nullable();
            $table->string('body', 20)->nullable();
            $table->unsignedTinyInteger('doors')->nullable();
            $table->enum('status', ['stock', 'sold', 'service', 'written_off'])->default('stock');
            $table->date('nct_expiry')->nullable();
            $table->json('photos')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('make');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
