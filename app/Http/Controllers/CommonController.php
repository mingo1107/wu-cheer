<?php
namespace App\Http\Controllers;

use App\Services\CommonService;
use App\Formatters\ApiOutput;
use Illuminate\Http\JsonResponse;

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
}
