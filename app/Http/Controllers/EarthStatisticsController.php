<?php
namespace App\Http\Controllers;

use App\Formatters\ApiOutput;
use App\Models\EarthData;
use App\Services\EarthStatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EarthStatisticsController extends Controller
{
    public function __construct(
        private readonly EarthStatisticsService $service,
        private readonly ApiOutput $apiOutput,
    ) {}

    // 取得指定工程的土單使用統計
    public function stats(Request $request, int $id): JsonResponse
    {
        try {
            $earthData = EarthData::query()->find($id);
            if (! $earthData) {
                return response()->json($this->apiOutput->failFormat('土單資料不存在', [], 404));
            }

            // 權限：限定同公司
            if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
                if ((int)$earthData->company_id !== (int)auth('api')->user()->company_id) {
                    return response()->json($this->apiOutput->failFormat('無權限存取', [], 403));
                }
            }

            $stats = $this->service->getUsageStats($earthData->id);

            return response()->json($this->apiOutput->successFormat([
                'earth_data_id' => $earthData->id,
                'flow_control_no' => $earthData->flow_control_no,
                'project_name' => $earthData->project_name,
                'totals' => $stats['totals'] ?? [],
                'daily' => $stats['daily'] ?? [],
            ], '統計資料取得成功'));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得統計資料失敗：' . $e->getMessage(), [], 500));
        }
    }
}
