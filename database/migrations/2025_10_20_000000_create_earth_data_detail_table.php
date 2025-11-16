<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('earth_data_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('earth_data_id')->index();
            $table->integer('status')->default(0)->comment('狀態：0:未列印/1:已列印/2:已使用/3:作廢');
            $table->string('barcode', 255)->unique();
            $table->date('use_start_date')->nullable()->comment('使用開始日期');
            $table->date('use_end_date')->nullable()->comment('使用結束日期');
            $table->timestamp('print_at')->comment('列印時間')->nullable();
            $table->timestamp('verified_at')->comment('驗證時間')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable()->comment('車輛 ID');
            $table->string('driver_name', 255)->nullable()->comment('司機名字');
            $table->timestamps();

            // Optional FK (uncomment if you want DB-level constraints)
            // $table->foreign('earth_data_id')->references('id')->on('earth_data')->onDelete('cascade');
            // $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('earth_data_detail');
    }
};
