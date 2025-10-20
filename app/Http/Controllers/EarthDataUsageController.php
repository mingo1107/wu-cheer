<?php
namespace App\Http\Controllers;

use App\Formatters\ApiOutput;
use App\Models\EarthData;
use App\Services\EarthDataUsageService;
use App\Exports\EarthDataDetailsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EarthDataUsageController extends Controller
{
    public function __construct(
        private readonly EarthDataUsageService $service,
        private readonly ApiOutput $apiOutput,
    ) {}

    // 取得指定工程的土單使用明細列表
    public function details(Request $request, int $id): JsonResponse
    {
        try {
            $earthData = EarthData::query()->find($id);
            if (! $earthData) {
                return response()->json($this->apiOutput->failFormat('土單資料不存在', [], 404));
            }

            if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
                if ((int)$earthData->company_id !== (int)auth('api')->user()->company_id) {
                    return response()->json($this->apiOutput->failFormat('無權限存取', [], 403));
                }
            }

            $details = $this->service->listDetailsWithUser($earthData->id);

            return response()->json($this->apiOutput->successFormat([
                'earth_data_id' => $earthData->id,
                'flow_control_no' => $earthData->flow_control_no,
                'project_name' => $earthData->project_name,
                'count' => $details->count(),
                'details' => $details,
            ], '土單使用明細取得成功'));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得土單使用明細失敗：' . $e->getMessage(), [], 500));
        }
    }

    // 匯出指定工程的土單使用明細（xlsx）
    public function detailsExport(Request $request, int $id)
    {
        try {
            $earthData = EarthData::query()->find($id);
            if (! $earthData) {
                return response()->json($this->apiOutput->failFormat('土單資料不存在', [], 404));
            }

            if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
                if ((int)$earthData->company_id !== (int)auth('api')->user()->company_id) {
                    return response()->json($this->apiOutput->failFormat('無權限存取', [], 403));
                }
            }

            $rows = $this->service->listDetailsWithUser($earthData->id);
            $filename = 'earth_usage_' . ($earthData->batch_no ?: 'data') . '_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new EarthDataDetailsExport($rows), $filename);
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('匯出失敗：' . $e->getMessage(), [], 500));
        }
    }
}
