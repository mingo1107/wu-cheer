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
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->index()->comment('操作使用者 ID');
            $table->bigInteger('company_id')->nullable()->index()->comment('公司 ID');
            $table->bigInteger('data_id')->default(0)->index()->comment('操作資料 ID');
            $table->string('controller', 100)->comment('控制器名稱');
            $table->string('method', 100)->comment('方法名稱');
            $table->string('ip', 45)->nullable()->comment('IP 位址');
            $table->text('requests_data')->nullable()->comment('請求資料 (JSON)');
            $table->tinyInteger('result')->default(0)->comment('操作結果 0:失敗 1:成功');
            $table->text('remark')->nullable()->comment('備註');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['company_id', 'created_at']);
            $table->index(['controller', 'method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_logs');
    }
};

