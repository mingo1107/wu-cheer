<?php

namespace App\Services;

use App\Repositories\UserLogRepository;
use Illuminate\Http\Request;

class BaseService
{
    protected $userLogRepository;

    public function __construct()
    {
        // 延遲載入 UserLogRepository，避免循環依賴
        $this->userLogRepository = app(UserLogRepository::class);
    }

    /**
     * 新增一筆使用者操作記錄
     *
     * @param array $params
     * @return \App\Models\UserLog
     */
    public function addUserLog(array $params = []): \App\Models\UserLog
    {
        $request = app('request');
        $route = $request->route();

        // 取得控制器和方法名稱
        $action = $route ? $route->getAction() : [];
        $controller = '';
        $method = '';

        if (!empty($action['controller'])) {
            $controllerParts = explode('@', class_basename($action['controller']));
            $controller = $controllerParts[0] ?? '';
            $method = $controllerParts[1] ?? '';
        }

        // 如果參數中有指定，則使用參數中的值
        if (!empty($params['controller'])) {
            $controller = $params['controller'];
        }
        if (!empty($params['method'])) {
            $method = $params['method'];
        }

        // 取得使用者資訊
        $user = auth('api')->user();

        // 準備記錄資料
        $logData = [
            'data_id'       => $params['data_id'] ?? 0,
            'user_id'       => $params['user_id'] ?? ($user ? $user->id : null),
            'company_id'    => $params['company_id'] ?? ($user ? $user->company_id : null),
            'controller'    => $controller,
            'method'        => $method,
            'ip'            => $params['ip'] ?? $request->ip(),
            'requests_data' => !empty($params['requests_data']) 
                ? (is_string($params['requests_data']) ? $params['requests_data'] : json_encode($params['requests_data']))
                : json_encode($request->except(['password', 'password_confirmation'])),
            'result'        => $params['result'] ?? 1,
            'remark'        => $params['remark'] ?? '',
        ];

        return $this->userLogRepository->create($logData);
    }

    /**
     * 記錄成功操作
     *
     * @param array $params
     * @return \App\Models\UserLog
     */
    public function logSuccess(array $params = []): \App\Models\UserLog
    {
        $params['result'] = 1;
        return $this->addUserLog($params);
    }

    /**
     * 記錄失敗操作
     *
     * @param array $params
     * @return \App\Models\UserLog
     */
    public function logFailure(array $params = []): \App\Models\UserLog
    {
        $params['result'] = 0;
        return $this->addUserLog($params);
    }
}

