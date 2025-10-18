<?php

namespace App\Http\Controllers;

use App\Services\AnnouncementService;
use App\Formatters\ApiOutput;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
{
    protected AnnouncementService $service;
    protected ApiOutput $api;

    public function __construct(AnnouncementService $service, ApiOutput $api)
    {
        $this->service = $service;
        $this->api = $api;
    }

    /**
     * 列表
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'search' => $request->get('search'),
                'is_active' => $request->get('is_active'),
                'starts_from' => $request->get('starts_from'),
                'ends_to' => $request->get('ends_to'),
                'sort_by' => $request->get('sort_by', 'starts_at'),
                'sort_order' => $request->get('sort_order', 'desc'),
            ];
            $perPage = (int) $request->get('per_page', 15);
            $data = $this->service->list($filters, $perPage);
            return response()->json($this->api->successFormat($data, '公告列表取得成功'));
        } catch (\Throwable $e) {
            return response()->json($this->api->failFormat('取得公告列表失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 新增
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'nullable|string',
                'starts_at' => 'nullable|date',
                'ends_at' => 'nullable|date|after_or_equal:starts_at',
                'is_active' => 'required|boolean',
            ], [
                'title.required' => '標題為必填',
                'ends_at.after_or_equal' => '結束時間需大於等於開始時間',
            ]);

            if ($validator->fails()) {
                return response()->json($this->api->failFormat('資料驗證失敗', $validator->errors(), 422));
            }

            $item = $this->service->create($request->only(['title','content','starts_at','ends_at','is_active']));
            return response()->json($this->api->successFormat($item, '公告建立成功'), 201);
        } catch (\Throwable $e) {
            return response()->json($this->api->failFormat('建立公告失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 詳細
     */
    public function show(int $id): JsonResponse
    {
        try {
            $item = $this->service->get($id);
            if (!$item) {
                return response()->json($this->api->failFormat('公告不存在', [], 404));
            }
            return response()->json($this->api->successFormat($item, '公告取得成功'));
        } catch (\Throwable $e) {
            return response()->json($this->api->failFormat('取得公告失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 更新
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'content' => 'nullable|string',
                'starts_at' => 'nullable|date',
                'ends_at' => 'nullable|date|after_or_equal:starts_at',
                'is_active' => 'sometimes|required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json($this->api->failFormat('資料驗證失敗', $validator->errors(), 422));
            }

            $item = $this->service->update($id, $request->only(['title','content','starts_at','ends_at','is_active']));
            if (!$item) {
                return response()->json($this->api->failFormat('公告不存在', [], 404));
            }
            return response()->json($this->api->successFormat($item, '公告更新成功'));
        } catch (\Throwable $e) {
            return response()->json($this->api->failFormat('更新公告失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 刪除
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->service->delete($id);
            if (!$deleted) {
                return response()->json($this->api->failFormat('公告不存在或刪除失敗', [], 404));
            }
            return response()->json($this->api->successFormat(null, '公告刪除成功'));
        } catch (\Throwable $e) {
            return response()->json($this->api->failFormat('刪除公告失敗：' . $e->getMessage(), [], 500));
        }
    }
}
