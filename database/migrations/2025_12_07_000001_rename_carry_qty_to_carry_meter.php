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
        // 在 earth_data_detail 表添加米數相關字段
        // meter_type: 10, 12, 14 分別代表米數
        // meter_quantity: 該米數類型的數量
        Schema::table('earth_data_detail', function (Blueprint $table) {
            if (!Schema::hasColumn('earth_data_detail', 'meter_type')) {
                $table->integer('meter_type')->nullable()->comment('米數類型：10, 12, 14');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 移除米數相關字段
        Schema::table('earth_data_detail', function (Blueprint $table) {
            if (Schema::hasColumn('earth_data_detail', 'meter_type')) {
                $table->dropColumn('meter_type');
            }
        });
    }
};
