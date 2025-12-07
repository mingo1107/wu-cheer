<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('earth_data', function (Blueprint $table) {
            $table->string('closure_status')->default('open')->after('status')->comment('結案狀態: open=進行中, closed=已結案');
            $table->timestamp('closed_at')->nullable()->after('closure_status')->comment('結案時間');
            $table->string('closed_by')->nullable()->after('closed_at')->comment('結案人員');
            $table->string('closure_certificate_path')->nullable()->after('closed_by')->comment('結案證明圖片路徑');
            $table->text('closure_remark')->nullable()->after('closure_certificate_path')->comment('結案備註');
        });
    }

    public function down(): void
    {
        Schema::table('earth_data', function (Blueprint $table) {
            $table->dropColumn([
                'closure_status',
                'closed_at',
                'closed_by',
                'closure_certificate_path',
                'closure_remark'
            ]);
        });
    }
};
