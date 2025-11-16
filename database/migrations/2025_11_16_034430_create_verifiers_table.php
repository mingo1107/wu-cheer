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
        Schema::create('verifiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('account')->unique()->index();
            $table->string('password');
            $table->string('name', 128)->nullable()->index();
            $table->string('email', 128)->nullable()->index();
            $table->string('phone', 16)->nullable()->index();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'account']);
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifiers');
    }
};
