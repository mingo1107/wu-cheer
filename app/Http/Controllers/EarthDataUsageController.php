<?php
namespace App\Http\Controllers;

use App\Formatters\ApiOutput;
use App\Models\EarthData;
use App\Models\EarthDataDetail;
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

            // 取得狀態篩選參數
            $statusFilter = $request->input('status');
            $status = null;
            if ($statusFilter !== null && $statusFilter !== '') {
                $status = (int) $statusFilter;
                // 驗證狀態值
                if (! in_array($status, [
                    \App\Models\EarthDataDetail::STATUS_UNPRINTED,
                    \App\Models\EarthDataDetail::STATUS_PRINTED,
                    \App\Models\EarthDataDetail::STATUS_USED,
                    \App\Models\EarthDataDetail::STATUS_VOIDED,
                    \App\Models\EarthDataDetail::STATUS_RECYCLED,
                ])) {
                    $status = null;
                }
            }

            $details = $this->service->listDetailsWithUser($earthData->id, $status);
            $stats = $this->service->getDetailStats($earthData->id);

            return response()->json($this->apiOutput->successFormat([
                'earth_data_id' => $earthData->id,
                'flow_control_no' => $earthData->flow_control_no,
                'project_name' => $earthData->project_name,
                'count' => $details->count(),
                'stats' => $stats,
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

    // 更新明細狀態（作廢）
    public function updateDetailStatus(Request $request, int $id, int $detailId): JsonResponse
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

            $detail = EarthDataDetail::query()->find($detailId);
            if (! $detail) {
                return response()->json($this->apiOutput->failFormat('明細不存在', [], 404));
            }

            if ((int)$detail->earth_data_id !== (int)$id) {
                return response()->json($this->apiOutput->failFormat('明細不屬於此土單', [], 400));
            }

            $status = (int) $request->input('status', EarthDataDetail::STATUS_VOIDED);
            
            // 驗證狀態值
            if (! in_array($status, [
                EarthDataDetail::STATUS_UNPRINTED,
                EarthDataDetail::STATUS_PRINTED,
                EarthDataDetail::STATUS_USED,
                EarthDataDetail::STATUS_VOIDED,
                EarthDataDetail::STATUS_RECYCLED,
            ])) {
                return response()->json($this->apiOutput->failFormat('無效的狀態值', [], 400));
            }

            $updated = $this->service->updateDetailStatus($detailId, $status);
            if (! $updated) {
                return response()->json($this->apiOutput->failFormat('更新狀態失敗', [], 500));
            }

            return response()->json($this->apiOutput->successFormat([
                'id' => $detailId,
                'status' => $status,
                'status_label' => EarthDataDetail::getStatusLabel($status),
            ], '更新狀態成功'));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('更新狀態失敗：' . $e->getMessage(), [], 500));
        }
    }

    // 批量回收明細
    public function recycleDetails(Request $request, int $id): JsonResponse
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

            $count = (int) $request->input('count', 0);
            if ($count <= 0) {
                return response()->json($this->apiOutput->failFormat('回收數量必須大於 0', [], 400));
            }

            $recycled = $this->service->recycleDetails($id, $count);
            if ($recycled === 0) {
                return response()->json($this->apiOutput->failFormat('無可回收的明細（需為已使用狀態）', [], 400));
            }

            return response()->json($this->apiOutput->successFormat([
                'recycled_count' => $recycled,
            ], "成功回收 {$recycled} 筆明細"));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('回收失敗：' . $e->getMessage(), [], 500));
        }
    }

    // 批量更新明細狀態
    public function batchUpdateStatus(Request $request, int $id): JsonResponse
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

            $detailIds = $request->input('detail_ids', []);
            $status = (int) $request->input('status');

            if (empty($detailIds) || ! is_array($detailIds)) {
                return response()->json($this->apiOutput->failFormat('請選擇要處理的明細', [], 400));
            }

            // 驗證狀態值
            if (! in_array($status, [
                EarthDataDetail::STATUS_VOIDED,
                EarthDataDetail::STATUS_RECYCLED,
            ])) {
                return response()->json($this->apiOutput->failFormat('無效的狀態值（僅支援作廢或回收）', [], 400));
            }

            // 驗證明細是否屬於此土單
            $details = EarthDataDetail::query()
                ->where('earth_data_id', $id)
                ->whereIn('id', $detailIds)
                ->get();

            if ($details->count() !== count($detailIds)) {
                return response()->json($this->apiOutput->failFormat('部分明細不存在或不屬於此土單', [], 400));
            }

            $updated = $this->service->batchUpdateStatusByIds($id, $detailIds, $status);
            if ($updated === 0) {
                return response()->json($this->apiOutput->failFormat('更新失敗', [], 500));
            }

            $actionLabel = $status === EarthDataDetail::STATUS_VOIDED ? '作廢' : '回收';
            return response()->json($this->apiOutput->successFormat([
                'updated_count' => $updated,
            ], "成功{$actionLabel} {$updated} 筆明細"));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('批量更新失敗：' . $e->getMessage(), [], 500));
        }
    }
}
