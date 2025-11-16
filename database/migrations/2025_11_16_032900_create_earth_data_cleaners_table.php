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
        Schema::create('earth_data_cleaners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('earth_data_id')->comment('土單ID');
            $table->unsignedBigInteger('cleaner_id')->comment('清運業者ID');
            $table->timestamps();

            // 索引
            $table->index(['earth_data_id', 'cleaner_id']);
            $table->index('cleaner_id');

            // 外鍵約束
            //$table->foreign('earth_data_id')->references('id')->on('earth_data')->onDelete('cascade');
            //$table->foreign('cleaner_id')->references('id')->on('cleaners')->onDelete('cascade');

            // 唯一約束：同一個土單不能有重複的清運業者
            $table->unique(['earth_data_id', 'cleaner_id'], 'earth_data_cleaner_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('earth_data_cleaners');
    }
};
