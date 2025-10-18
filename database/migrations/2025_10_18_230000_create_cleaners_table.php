<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cleaners', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->index();
            $table->string('cleaner_name')->comment('清運業者名稱');
            $table->string('tax_id')->nullable()->comment('統一編號');
            $table->string('contact_person')->comment('聯絡人');
            $table->string('phone')->comment('聯絡電話');
            $table->string('status')->default('active');
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'cleaner_name']);
            $table->index(['company_id', 'tax_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cleaners');
    }
};
