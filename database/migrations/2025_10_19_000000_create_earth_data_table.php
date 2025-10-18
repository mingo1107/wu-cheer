<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('earth_data', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->index();
            $table->string('batch_no')->index()->comment('批號');
            $table->string('doc_seq_detail')->nullable()->comment('文件序號明細');
            $table->date('issue_date')->nullable()->comment('開立日期');
            $table->integer('issue_count')->default(0)->comment('開立張數');
            $table->string('customer_code')->nullable()->comment('客戶代號');
            $table->date('valid_from')->nullable()->comment('有效期限（起）');
            $table->date('valid_to')->nullable()->comment('有效期限（迄）');
            $table->string('cleaner_name')->nullable()->comment('土方清運業者');
            $table->string('project_name')->nullable()->comment('工程名稱');
            $table->string('flow_control_no')->nullable()->comment('工程流向管制編號');
            $table->decimal('carry_qty', 12, 2)->default(0)->comment('載運數量');
            $table->string('carry_soil_type')->nullable()->comment('載運土質');
            $table->string('status_desc')->nullable()->comment('狀態說明');
            $table->text('remark_desc')->nullable()->comment('備註說明');
            $table->string('created_by')->nullable()->comment('建檔人員');
            $table->string('updated_by')->nullable()->comment('修改人員');
            $table->string('sys_serial_no')->nullable()->comment('系統流水號');
            $table->string('status')->nullable()->default('active')->comment('狀態');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'batch_no']);
            $table->index(['company_id', 'customer_code']);
            $table->index(['company_id', 'issue_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('earth_data');
    }
};
