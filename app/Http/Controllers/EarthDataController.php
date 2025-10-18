<?php
namespace App\Http\Controllers;

use App\Formatters\ApiOutput;
use App\Services\EarthDataService;
use App\Models\EarthData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EarthDataController extends Controller
{
    public function __construct(private EarthDataService $service, private ApiOutput $out)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search'          => $request->get('search'),
            'issue_date_from' => $request->get('issue_date_from'),
            'issue_date_to'   => $request->get('issue_date_to'),
            'sort_by'         => $request->get('sort_by', 'created_at'),
            'sort_order'      => $request->get('sort_order', 'desc'),
        ];
        $perPage = (int) $request->get('per_page', 100);
        $data = $this->service->list($filters, $perPage);
        return response()->json($this->out->successFormat($data, '土單資料列表取得成功'));
    }

    public function bulkUpsert(Request $request): JsonResponse
    {
        $rows = $request->input('rows', []);
        if (!is_array($rows)) {
            return response()->json($this->out->failFormat('參數格式錯誤', [], 422));
        }

        $rules = [
            'batch_no'       => 'required|string|max:255',
            'doc_seq_detail' => 'nullable|string|max:255',
            'issue_date'     => 'nullable|date',
            'issue_count'    => 'nullable|integer|min:0',
            'customer_code'  => 'nullable|string|max:255',
            'valid_from'     => 'nullable|date',
            'valid_to'       => 'nullable|date|after_or_equal:valid_from',
            'cleaner_name'   => 'nullable|string|max:255',
            'project_name'   => 'nullable|string|max:255',
            'flow_control_no'=> 'nullable|string|max:255',
            'carry_qty'      => 'nullable|numeric|min:0',
            'carry_soil_type'=> 'nullable|string|max:255',
            'status_desc'    => 'nullable|string|max:255',
            'remark_desc'    => 'nullable|string',
            'created_by'     => 'nullable|string|max:255',
            'updated_by'     => 'nullable|string|max:255',
            'sys_serial_no'  => 'nullable|string|max:255',
            'status'         => 'nullable|string|max:50',
        ];

        foreach ($rows as $i => $row) {
            $v = Validator::make($row, $rules);
            if ($v->fails()) {
                return response()->json($this->out->failFormat("第" . ($i + 1) . "筆資料驗證失敗", $v->errors(), 422));
            }
        }

        try {
            $affected = $this->service->bulkUpsert($rows);
            return response()->json($this->out->successFormat(['affected' => $affected], '批次儲存成功'));
        } catch (\Throwable $e) {
            Log::error('EarthData 批次儲存失敗', ['error' => $e->getMessage()]);
            return response()->json($this->out->failFormat('批次儲存失敗: ' . $e->getMessage(), [], 500));
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json($this->out->failFormat('請提供要刪除的 ID 陣列', [], 422));
        }

        try {
            $deleted = $this->service->bulkDelete($ids);
            return response()->json($this->out->successFormat(['deleted' => $deleted], '批次刪除成功'));
        } catch (\Throwable $e) {
            Log::error('EarthData 批次刪除失敗', ['error' => $e->getMessage()]);
            return response()->json($this->out->failFormat('批次刪除失敗: ' . $e->getMessage(), [], 500));
        }
    }

    public function show(int $id): JsonResponse
    {
        // schema for id=0 only for now
        if ($id === 0) {
            $schema = [];
            foreach (EarthData::FILLABLE as $field) {
                $schema[$field] = EarthData::ATTRIBUTES[$field] ?? null;
            }
            return response()->json($this->out->successFormat($schema, '土單資料欄位預設值'));
        }

        // non-schema fetch is not defined yet (excel sheet operates in bulk). Return 404
        return response()->json($this->out->failFormat('不支援單筆查詢，請使用列表或批次 API', [], 404));
    }
}
