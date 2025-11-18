<?php

namespace App\Http\Controllers;

use App\Formatters\ApiOutput;
use App\Services\UserLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserLogController extends Controller
{
    protected $userLogService;
    protected $apiOutput;

    public function __construct(UserLogService $userLogService, ApiOutput $apiOutput)
    {
        $this->userLogService = $userLogService;
        $this->apiOutput = $apiOutput;
    }

    /**
     * 取得使用者操作記錄列表
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'user_id'    => $request->get('user_id'),
                'controller' => $request->get('controller'),
                'method'     => $request->get('method'),
                'result'     => $request->get('result'),
                'date_from'  => $request->get('date_from'),
                'date_to'    => $request->get('date_to'),
                'sort_by'    => $request->get('sort_by', 'created_at'),
                'sort_order' => $request->get('sort_order', 'desc'),
            ];

            $perPage = (int) $request->get('per_page', 15);
            $logs = $this->userLogService->getLogs($filters, $perPage);

            return response()->json($this->apiOutput->successFormat($logs, '使用者操作記錄列表取得成功'));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得使用者操作記錄列表失敗：' . $e->getMessage(), [], 500));
        }
    }
}

