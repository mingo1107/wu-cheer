<?php

namespace App\Http\Controllers;

use App\Services\CommonService;
use App\Formatters\ApiOutput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    protected CommonService $service;
    protected ApiOutput $apiOutput;

    public function __construct(CommonService $service, ApiOutput $apiOutput)
    {
        $this->service   = $service;
        $this->apiOutput = $apiOutput;
    }

    public function getCleanerList(): JsonResponse
    {
        try {
            $list = $this->service->getCleanerList();
            return response()->json($this->apiOutput->successFormat($list, '清運業者列表取得成功'));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得清運業者列表失敗：' . $e->getMessage(), [], 500));
        }
    }

    public function getCustomerList(): JsonResponse
    {
        try {
            $list = $this->service->getCustomerList();
            return response()->json($this->apiOutput->successFormat($list, '客戶列表取得成功'));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得客戶列表失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 取得土單工程 datalist（可搜尋、狀態過濾）
     * status: all|active|inactive
     * q: 關鍵字（批號/工程/客戶）
     */
    public function getEarthDataDatalist(Request $request): JsonResponse
    {
        try {
            $status = $request->get('status', 'all');
            $q      = trim((string)$request->get('q', ''));

            $data = $this->service->getEarthDataDatalist($status, $q);

            return response()->json($this->apiOutput->successFormat($data, '工程清單取得成功'));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得工程清單失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 取得土單明細狀態列表
     */
    public function getEarthDataDetailStatusList(): JsonResponse
    {
        try {
            $statusList = $this->service->getEarthDataDetailStatusList();

            return response()->json($this->apiOutput->successFormat($statusList, '狀態列表取得成功'));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得狀態列表失敗：' . $e->getMessage(), [], 500));
        }
    }
}
