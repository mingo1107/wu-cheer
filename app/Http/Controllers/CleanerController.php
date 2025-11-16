<?php

namespace App\Http\Controllers;

use App\Formatters\ApiOutput;
use App\Services\CleanerService;
use App\Services\CleanerVehicleService;
use App\Models\Cleaner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CleanerController extends Controller
{
    protected $service;
    protected $vehicleService;
    protected $out;

    public function __construct(CleanerService $service, CleanerVehicleService $vehicleService, ApiOutput $out)
    {
        $this->service = $service;
        $this->vehicleService = $vehicleService;
        $this->out = $out;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search'     => $request->get('search'),
            'status'     => $request->get('status'),
            'sort_by'    => $request->get('sort_by', 'created_at'),
            'sort_order' => $request->get('sort_order', 'desc'),
        ];
        $perPage = (int) $request->get('per_page', 15);
        $data = $this->service->list($filters, $perPage);
        return response()->json($this->out->successFormat($data, '清運業者列表取得成功'));
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cleaner_name'   => 'required|string|max:255',
            'tax_id'         => 'nullable|string|size:8|regex:/^\d{8}$/',
            'contact_person' => 'required|string|max:255',
            'phone'          => 'required|string|max:50',
            'status'         => 'nullable|in:active,inactive',
            'notes'          => 'nullable|string|max:1000',
        ], [
            'cleaner_name.required'   => '清運業者名稱為必填',
            'contact_person.required' => '聯絡人為必填',
            'phone.required'          => '聯絡電話為必填',
            'tax_id.size'             => '統一編號必須為8位數字',
            'tax_id.regex'            => '統一編號格式不正確',
        ]);

        if ($validator->fails()) {
            return response()->json($this->out->failFormat('資料驗證失敗', $validator->errors(), 422));
        }

        try {
            $item = $this->service->create($request->all());
            return response()->json($this->out->successFormat($item, '清運業者建立成功'));
        } catch (\Exception $e) {
            Log::error('清運業者建立失敗', ['error' => $e->getMessage()]);
            return response()->json($this->out->failFormat($e->getMessage(), [], 422));
        }
    }

    public function show(int $id): JsonResponse
    {
        if ($id === 0) {
            $schema = [];
            foreach (Cleaner::FILLABLE as $field) {
                $schema[$field] = Cleaner::ATTRIBUTES[$field] ?? null;
            }
            return response()->json($this->out->successFormat($schema, '清運業者欄位預設值'));
        }

        $item = $this->service->get($id);
        if (!$item) {
            return response()->json($this->out->failFormat('清運業者不存在', [], 404));
        }
        return response()->json($this->out->successFormat($item, '清運業者取得成功'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cleaner_name'   => 'sometimes|required|string|max:255',
            'tax_id'         => 'nullable|string|size:8|regex:/^\d{8}$/',
            'contact_person' => 'sometimes|required|string|max:255',
            'phone'          => 'sometimes|required|string|max:50',
            'status'         => 'nullable|in:active,inactive',
            'notes'          => 'nullable|string|max:1000',
        ]);
        if ($validator->fails()) {
            return response()->json($this->out->failFormat('資料驗證失敗', $validator->errors(), 422));
        }

        try {
            $item = $this->service->update($id, $request->all());
            if (!$item) {
                return response()->json($this->out->failFormat('清運業者不存在或更新失敗', [], 404));
            }
            return response()->json($this->out->successFormat($item, '清運業者更新成功'));
        } catch (\Exception $e) {
            Log::error('清運業者更新失敗', ['error' => $e->getMessage()]);
            return response()->json($this->out->failFormat($e->getMessage(), [], 422));
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $ok = $this->service->delete($id);
        if (!$ok) {
            return response()->json($this->out->failFormat('清運業者不存在或刪除失敗', [], 404));
        }
        return response()->json($this->out->successFormat(['id' => $id], '清運業者刪除成功'));
    }

    // ========== 車輛管理方法 ==========

    /**
     * 取得清運業者的所有車輛
     */
    public function getVehicles(int $cleanerId): JsonResponse
    {
        try {
            $vehicles = $this->vehicleService->getByCleanerId($cleanerId);
            return response()->json($this->out->successFormat($vehicles, '車輛列表取得成功'));
        } catch (\Exception $e) {
            Log::error('取得車輛列表失敗', ['error' => $e->getMessage(), 'cleaner_id' => $cleanerId]);
            return response()->json($this->out->failFormat($e->getMessage(), [], 422));
        }
    }

    /**
     * 建立車輛
     */
    public function storeVehicle(Request $request, int $cleanerId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'front_plate' => 'required|string|max:20',
            'rear_plate'  => 'nullable|string|max:20',
            'status'      => 'nullable|in:active,inactive',
            'notes'     => 'nullable|string|max:1000',
        ], [
            'front_plate.required' => '車頭車號為必填',
            'front_plate.max'      => '車頭車號不能超過20個字元',
            'rear_plate.max'        => '車尾車號不能超過20個字元',
            'status.in'             => '狀態值不正確',
            'notes.max'             => '備註不能超過1000個字元',
        ]);

        if ($validator->fails()) {
            return response()->json($this->out->failFormat('資料驗證失敗', $validator->errors(), 422));
        }

        try {
            $data = $request->all();
            $data['cleaner_id'] = $cleanerId;
            $vehicle = $this->vehicleService->create($data);
            return response()->json($this->out->successFormat($vehicle, '車輛建立成功'));
        } catch (\Exception $e) {
            Log::error('車輛建立失敗', ['error' => $e->getMessage(), 'cleaner_id' => $cleanerId]);
            return response()->json($this->out->failFormat($e->getMessage(), [], 422));
        }
    }

    /**
     * 更新車輛
     */
    public function updateVehicle(Request $request, int $cleanerId, int $vehicleId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'front_plate' => 'sometimes|required|string|max:20',
            'rear_plate'  => 'nullable|string|max:20',
            'status'      => 'nullable|in:active,inactive',
            'notes'       => 'nullable|string|max:1000',
        ], [
            'front_plate.required' => '車頭車號為必填',
            'front_plate.max'      => '車頭車號不能超過20個字元',
            'rear_plate.max'       => '車尾車號不能超過20個字元',
            'status.in'            => '狀態值不正確',
            'notes.max'            => '備註不能超過1000個字元',
        ]);

        if ($validator->fails()) {
            return response()->json($this->out->failFormat('資料驗證失敗', $validator->errors(), 422));
        }

        try {
            $vehicle = $this->vehicleService->update($vehicleId, $request->all());
            if (!$vehicle) {
                return response()->json($this->out->failFormat('車輛不存在或更新失敗', [], 404));
            }
            return response()->json($this->out->successFormat($vehicle, '車輛更新成功'));
        } catch (\Exception $e) {
            Log::error('車輛更新失敗', ['error' => $e->getMessage(), 'vehicle_id' => $vehicleId]);
            return response()->json($this->out->failFormat($e->getMessage(), [], 422));
        }
    }

    /**
     * 刪除車輛
     */
    public function destroyVehicle(int $cleanerId, int $vehicleId): JsonResponse
    {
        try {
            $ok = $this->vehicleService->delete($vehicleId);
            if (!$ok) {
                return response()->json($this->out->failFormat('車輛不存在或刪除失敗', [], 404));
            }
            return response()->json($this->out->successFormat(['id' => $vehicleId], '車輛刪除成功'));
        } catch (\Exception $e) {
            Log::error('車輛刪除失敗', ['error' => $e->getMessage(), 'vehicle_id' => $vehicleId]);
            return response()->json($this->out->failFormat($e->getMessage(), [], 422));
        }
    }
}
