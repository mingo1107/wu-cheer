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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->index();
            $table->string('customer_name')->comment('公司名稱');
            $table->string('contact_person')->comment('聯絡人');
            $table->string('phone')->nullable()->comment('電話');
            $table->string('email')->nullable()->comment('電子郵件');
            $table->text('address')->nullable()->comment('地址');
            $table->string('tax_id')->nullable()->comment('統一編號');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('狀態');
            $table->text('notes')->nullable()->comment('備註');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'customer_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
