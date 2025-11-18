<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Formatters\ApiOutput;

class CheckAdminRole
{
    protected $apiOutput;

    public function __construct(ApiOutput $apiOutput)
    {
        $this->apiOutput = $apiOutput;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();
        
        // 檢查使用者是否已登入
        if (!$user) {
            return response()->json(
                $this->apiOutput->failFormat('請先登入', [], 401),
                401
            );
        }

        // 檢查是否為管理員 (role === 0)
        if ($user->role !== 0) {
            return response()->json(
                $this->apiOutput->failFormat('您沒有權限執行此操作', [], 403),
                403
            );
        }

        return $next($request);
    }
}

