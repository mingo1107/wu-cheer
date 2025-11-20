<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Formatters\ApiOutput;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    protected DashboardService $service;
    protected ApiOutput $apiOutput;

    public function __construct(DashboardService $service, ApiOutput $apiOutput)
    {
        $this->service = $service;
        $this->apiOutput = $apiOutput;
    }

    /**
     * 取得儀表板統計資料
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->service->getDashboardStats();
            return response()->json($this->apiOutput->successFormat($stats, '取得儀表板統計資料成功'));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得儀表板統計資料失敗：' . $e->getMessage(), [], 500));
        }
    }
}

