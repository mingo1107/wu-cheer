<?php
namespace App\Http\Controllers;

use App\Formatters\ApiOutput;
use App\Models\EarthData;
use App\Services\EarthDataService;
// repositories should be consumed via services per project conventions
use App\Exports\EarthDataDetailsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EarthDataController extends Controller
{
    protected EarthDataService $service;
    protected ApiOutput $apiOutput;

    public function __construct(EarthDataService $service, ApiOutput $apiOutput)
    {
        $this->service   = $service;
        $this->apiOutput = $apiOutput;
    }

    /**
     * 匯出指定工程的土單使用明細（CSV，Excel 可開啟）
     */
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

    /**
     * 取得指定工程的土單使用明細列表
     */
    public function details(Request $request, int $id): JsonResponse
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

    /**
     * 增加/減少土單張數（細項）
     */
    public function adjustDetails(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'action' => 'required|in:add,remove',
                'count'  => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json($this->apiOutput->failFormat('資料驗證失敗', $validator->errors(), 422));
            }

            $action = $request->get('action');
            $count  = (int) $request->get('count');
            $result = $this->service->adjustDetails($id, $action, $count);

            return response()->json($this->apiOutput->successFormat(array_merge($result, [
                'earth_data_id' => $id,
            ]), '土單張數調整完成'));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('調整土單張數失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 取得土單資料列表
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'search'          => $request->get('search'),
                'issue_date_from' => $request->get('issue_date_from'),
                'issue_date_to'   => $request->get('issue_date_to'),
                'sort_by'         => $request->get('sort_by', 'created_at'),
                'sort_order'      => $request->get('sort_order', 'desc'),
            ];

            $perPage = (int) $request->get('per_page', 15);
            $list    = $this->service->getEarthDataList($filters, $perPage);

            return response()->json($this->apiOutput->successFormat($list, '土單資料列表取得成功'));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得土單資料列表失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 建立土單資料
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'batch_no'        => 'required|string|max:255',
                'cleaner_id'      => 'nullable|integer',
                'project_name'    => 'nullable|string|max:255',
                'issue_date'      => 'nullable|date',
                'issue_count'     => 'nullable|integer|min:0',
                'customer_id'     => 'nullable|integer|exists:customers,id',
                'valid_date_from' => 'nullable|date',
                'valid_date_to'   => 'nullable|date|after_or_equal:valid_date_from',
                'flow_control_no' => 'nullable|string|max:255',
                'carry_qty'       => 'nullable|numeric|min:0',
                'carry_soil_type' => 'nullable|string|max:255',
                'status_desc'     => 'nullable|string|max:500',
                'remark_desc'     => 'nullable|string|max:1000',
                //'sys_serial_no'   => 'nullable|string|max:255',
                'status'          => 'nullable|in:active,inactive',
            ], [
                'batch_no.required' => '批號為必填欄位',
            ]);

            if ($validator->fails()) {
                return response()->json($this->apiOutput->failFormat('資料驗證失敗', $validator->errors(), 422));
            }

            $data   = $request->all();
            $userId = auth('api')->check() ? auth('api')->id() : null;
            if ($userId) {
                $data['created_by'] = $userId;
                $data['updated_by'] = $userId;
            }

            $item = $this->service->createEarthData($data);

            return response()->json($this->apiOutput->successFormat($item, '土單資料建立成功'), 201);
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('建立土單資料失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 取得單筆土單資料或欄位預設
     */
    public function show(int $id): JsonResponse
    {
        try {
            if ($id === 0) {
                $schema = [];
                foreach (EarthData::FILLABLE as $field) {
                    $schema[$field] = EarthData::ATTRIBUTES[$field] ?? null;
                }
                return response()->json($this->apiOutput->successFormat($schema, '土單欄位預設值'));
            }

            $item = $this->service->getEarthData($id);
            if (! $item) {
                return response()->json($this->apiOutput->failFormat('土單資料不存在', [], 404));
            }

            return response()->json($this->apiOutput->successFormat($item, '土單資料取得成功'));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得土單資料失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 更新土單資料
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'batch_no'        => 'sometimes|required|string|max:255',
                'cleaner_id'      => 'nullable|integer',
                'project_name'    => 'nullable|string|max:255',
                'flow_control_no' => 'nullable|string|max:255',
                'issue_date'      => 'nullable|date',
                'issue_count'     => 'nullable|integer|min:0',
                'customer_id'     => 'nullable|integer|exists:customers,id',
                'valid_date_from' => 'nullable|date',
                'valid_date_to'   => 'nullable|date|after_or_equal:valid_date_from',
                'carry_qty'       => 'nullable|numeric|min:0',
                'carry_soil_type' => 'nullable|string|max:255',
                'status_desc'     => 'nullable|string|max:500',
                'remark_desc'     => 'nullable|string|max:1000',
                'sys_serial_no'   => 'nullable|string|max:255',
                'status'          => 'nullable|in:active,inactive',
                'created_by'      => 'nullable|integer',
                'updated_by'      => 'nullable|integer',
            ], [
                'batch_no.required' => '批號為必填欄位',
            ]);

            if ($validator->fails()) {
                return response()->json($this->apiOutput->failFormat('資料驗證失敗', $validator->errors(), 422));
            }

            $data   = $request->all();
            $userId = auth('api')->check() ? auth('api')->id() : null;
            if ($userId) {
                $data['updated_by'] = $userId;
            }

            $item = $this->service->updateEarthData($id, $data);

            return response()->json($this->apiOutput->successFormat($item, '土單資料更新成功'));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('更新土單資料失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 刪除土單資料
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->service->deleteEarthData($id);

            if (! $deleted) {
                return response()->json($this->apiOutput->failFormat('土單資料不存在或刪除失敗', [], 404));
            }

            return response()->json($this->apiOutput->successFormat(null, '土單資料刪除成功'));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('刪除土單資料失敗：' . $e->getMessage(), [], 500));
        }
    }
}
