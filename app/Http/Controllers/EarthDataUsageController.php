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
use Illuminate\Support\Facades\Validator;

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
                $this->service->logFailure([
                    'data_id' => $detailId,
                    'remark' => '更新明細狀態失敗：無效的狀態值（土單 ID：' . $id . '，明細 ID：' . $detailId . '）'
                ]);
                return response()->json($this->apiOutput->failFormat('無效的狀態值', [], 400));
            }

            $updated = $this->service->updateDetailStatus($detailId, $status);
            if (! $updated) {
                $this->service->logFailure([
                    'data_id' => $detailId,
                    'remark' => '更新明細狀態失敗：更新失敗（土單 ID：' . $id . '，明細 ID：' . $detailId . '）'
                ]);
                return response()->json($this->apiOutput->failFormat('更新狀態失敗', [], 500));
            }

            // 記錄使用者操作
            $this->service->logSuccess([
                'data_id' => $detailId,
                'remark' => '更新明細狀態（土單 ID：' . $id . '，明細 ID：' . $detailId . '，狀態：' . EarthDataDetail::getStatusLabel($status) . '）'
            ]);

            return response()->json($this->apiOutput->successFormat([
                'id' => $detailId,
                'status' => $status,
                'status_label' => EarthDataDetail::getStatusLabel($status),
            ], '更新狀態成功'));
        } catch (\Exception $e) {
            $this->service->logFailure([
                'data_id' => $detailId ?? 0,
                'remark' => '更新明細狀態系統錯誤：' . $e->getMessage() . '（土單 ID：' . $id . '）'
            ]);
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
                $this->service->logFailure([
                    'data_id' => $id,
                    'remark' => '批量回收明細失敗：回收數量必須大於 0（土單 ID：' . $id . '）'
                ]);
                return response()->json($this->apiOutput->failFormat('回收數量必須大於 0', [], 400));
            }

            $recycled = $this->service->recycleDetails($id, $count);
            if ($recycled === 0) {
                $this->service->logFailure([
                    'data_id' => $id,
                    'remark' => '批量回收明細失敗：無可回收的明細（需為已使用狀態）（土單 ID：' . $id . '）'
                ]);
                return response()->json($this->apiOutput->failFormat('無可回收的明細（需為已使用狀態）', [], 400));
            }

            // 記錄使用者操作
            $this->service->logSuccess([
                'data_id' => $id,
                'remark' => '批量回收明細（土單 ID：' . $id . '，請求數量：' . $count . '，成功回收：' . $recycled . ' 筆）'
            ]);

            return response()->json($this->apiOutput->successFormat([
                'recycled_count' => $recycled,
            ], "成功回收 {$recycled} 筆明細"));
        } catch (\Exception $e) {
            $this->service->logFailure([
                'data_id' => $id,
                'remark' => '批量回收明細系統錯誤：' . $e->getMessage() . '（土單 ID：' . $id . '）'
            ]);
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
                $this->service->logFailure([
                    'data_id' => $id,
                    'remark' => '批量更新明細狀態失敗：無效的狀態值（僅支援作廢或回收）（土單 ID：' . $id . '）'
                ]);
                return response()->json($this->apiOutput->failFormat('無效的狀態值（僅支援作廢或回收）', [], 400));
            }

            // 驗證明細是否屬於此土單
            $details = EarthDataDetail::query()
                ->where('earth_data_id', $id)
                ->whereIn('id', $detailIds)
                ->get();

            if ($details->count() !== count($detailIds)) {
                $this->service->logFailure([
                    'data_id' => $id,
                    'remark' => '批量更新明細狀態失敗：部分明細不存在或不屬於此土單（土單 ID：' . $id . '，請求 ' . count($detailIds) . ' 筆，找到 ' . $details->count() . ' 筆）'
                ]);
                return response()->json($this->apiOutput->failFormat('部分明細不存在或不屬於此土單', [], 400));
            }

            $updated = $this->service->batchUpdateStatusByIds($id, $detailIds, $status);
            if ($updated === 0) {
                $this->service->logFailure([
                    'data_id' => $id,
                    'remark' => '批量更新明細狀態失敗：更新失敗（土單 ID：' . $id . '）'
                ]);
                return response()->json($this->apiOutput->failFormat('更新失敗', [], 500));
            }

            $actionLabel = $status === EarthDataDetail::STATUS_VOIDED ? '作廢' : '回收';
            
            // 記錄使用者操作
            $this->service->logSuccess([
                'data_id' => $id,
                'remark' => '批量更新明細狀態（土單 ID：' . $id . '，操作：' . $actionLabel . '，請求 ' . count($detailIds) . ' 筆，成功 ' . $updated . ' 筆）'
            ]);

            return response()->json($this->apiOutput->successFormat([
                'updated_count' => $updated,
            ], "成功{$actionLabel} {$updated} 筆明細"));
        } catch (\Exception $e) {
            $this->service->logFailure([
                'data_id' => $id,
                'remark' => '批量更新明細狀態系統錯誤：' . $e->getMessage() . '（土單 ID：' . $id . '）'
            ]);
            return response()->json($this->apiOutput->failFormat('批量更新失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 批量更新明細的使用起訖日期
     */
    public function batchUpdateDates(Request $request, int $id): JsonResponse
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

            $validator = Validator::make($request->all(), [
                'detail_ids'     => 'required|array',
                'detail_ids.*'   => 'integer',
                'use_start_date' => 'nullable|date',
                'use_end_date'   => 'nullable|date|after_or_equal:use_start_date',
            ]);

            if ($validator->fails()) {
                $this->service->logFailure([
                    'data_id' => $id,
                    'remark' => '批量更新明細日期驗證失敗：' . json_encode($validator->errors()->all()) . '（土單 ID：' . $id . '）'
                ]);
                return response()->json($this->apiOutput->failFormat('資料驗證失敗', $validator->errors(), 422));
            }

            $detailIds    = $request->input('detail_ids', []);
            $useStartDate = $request->input('use_start_date');
            $useEndDate   = $request->input('use_end_date');

            if (empty($detailIds)) {
                $this->service->logFailure([
                    'data_id' => $id,
                    'remark' => '批量更新明細日期失敗：請選擇要更新的明細（土單 ID：' . $id . '）'
                ]);
                return response()->json($this->apiOutput->failFormat('請選擇要更新的明細', [], 400));
            }

            // 驗證明細是否屬於此土單
            $details = EarthDataDetail::query()
                ->where('earth_data_id', $id)
                ->whereIn('id', $detailIds)
                ->get();

            if ($details->count() !== count($detailIds)) {
                $this->service->logFailure([
                    'data_id' => $id,
                    'remark' => '批量更新明細日期失敗：部分明細不存在或不屬於此土單（土單 ID：' . $id . '，請求 ' . count($detailIds) . ' 筆，找到 ' . $details->count() . ' 筆）'
                ]);
                return response()->json($this->apiOutput->failFormat('部分明細不存在或不屬於此土單', [], 400));
            }

            $updated = $this->service->batchUpdateDatesByIds($id, $detailIds, $useStartDate, $useEndDate);

            // 記錄使用者操作
            $this->service->logSuccess([
                'data_id' => $id,
                'remark' => '批量更新明細日期（土單 ID：' . $id . '，請求 ' . count($detailIds) . ' 筆，成功 ' . $updated . ' 筆，起日：' . ($useStartDate ?? '無') . '，迄日：' . ($useEndDate ?? '無') . '）'
            ]);

            return response()->json($this->apiOutput->successFormat([
                'updated_count' => $updated,
            ], '批量更新日期成功'));
        } catch (\Exception $e) {
            $this->service->logFailure([
                'data_id' => $id,
                'remark' => '批量更新明細日期系統錯誤：' . $e->getMessage() . '（土單 ID：' . $id . '）'
            ]);
            return response()->json($this->apiOutput->failFormat('批量更新日期失敗：' . $e->getMessage(), [], 500));
        }
    }
}
