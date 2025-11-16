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
        Schema::create('cleaner_vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cleaner_id')->index()->comment('清運業者ID');
            $table->string('front_plate', 20)->comment('車頭車號（必填）');
            $table->string('rear_plate', 20)->nullable()->comment('車尾車號（選填）');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('狀態');
            $table->text('notes')->nullable()->comment('備註');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index(['cleaner_id', 'status']);
            $table->index('front_plate');

            // 外鍵約束
            //$table->foreign('cleaner_id')->references('id')->on('cleaners')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaner_vehicles');
    }
};
