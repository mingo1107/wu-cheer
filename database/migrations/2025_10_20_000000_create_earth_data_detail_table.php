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
            $table->string('barcode', 255)->unique();
            $table->timestamp('verified_at')->comment('驗證時間')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
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
