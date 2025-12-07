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
        Schema::table('earth_data', function (Blueprint $table) {
            // 修改 carry_soil_type 為 JSON 類型
            $table->json('carry_soil_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('earth_data', function (Blueprint $table) {
            // 還原為 VARCHAR
            $table->string('carry_soil_type', 50)->nullable()->change();
        });
    }
};
