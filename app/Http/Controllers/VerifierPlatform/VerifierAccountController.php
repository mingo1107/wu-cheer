<?php

namespace App\Http\Controllers\VerifierPlatform;

use App\Http\Controllers\Controller;
use App\Services\VerifierPlatform\VerifierAccountService;
use App\Formatters\ApiOutput;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class VerifierAccountController extends Controller
{
    protected $verifierAccountService;
    protected $apiOutput;

    public function __construct(VerifierAccountService $verifierAccountService, ApiOutput $apiOutput)
    {
        $this->verifierAccountService = $verifierAccountService;
        $this->apiOutput = $apiOutput;
    }

    /**
     * 核銷人員登入
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            // 記錄登入嘗試
            Log::info('核銷人員登入嘗試', [
                'account' => $request->input('account'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // 驗證請求參數
            $validator = Validator::make($request->all(), [
                'account' => 'required|string|max:255',
                'password' => 'required|string|min:6|max:255',
            ], [
                'account.required' => '帳號為必填欄位',
                'account.max' => '帳號長度不能超過255個字元',
                'password.required' => '密碼為必填欄位',
                'password.min' => '密碼至少需要6個字元',
                'password.max' => '密碼長度不能超過255個字元',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    $this->apiOutput->failFormat('驗證失敗', $validator->errors(), 422),
                    422
                );
            }

            // 呼叫 Service 處理登入邏輯
            $data = $this->verifierAccountService->login($request->only(['account', 'password']));

            // 記錄登入結果
            Log::info('核銷人員登入成功', [
                'account' => $data['verifier']['account']
            ]);

            return response()->json(
                $this->apiOutput->successFormat($data, '登入成功'),
                200
            );
        } catch (\Exception $e) {
            // 檢查是否為認證相關錯誤
            $authErrors = [
                '帳號不存在或密碼錯誤',
                '帳號已被停用',
                '帳號和密碼為必填欄位'
            ];

            if (in_array($e->getMessage(), $authErrors)) {
                // 認證錯誤，返回 200 但 status 為 false
                Log::info('核銷人員登入認證失敗', [
                    'account' => $request->input('account'),
                    'error' => $e->getMessage()
                ]);

                return response()->json(
                    $this->apiOutput->failFormat($e->getMessage(), [], 200),
                    200
                );
            } else {
                // 真正的系統錯誤，返回 500
                Log::error('核銷人員登入系統錯誤', [
                    'account' => $request->input('account'),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json(
                    $this->apiOutput->exceptionFormat('系統錯誤，請稍後再試', 500, $e),
                    500
                );
            }
        }
    }

    /**
     * 核銷人員登出
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // 記錄登出操作
            Log::info('核銷人員登出', [
                'account' => auth('verifier')->user()?->account,
                'ip' => $request->ip()
            ]);

            $this->verifierAccountService->logout();

            return response()->json(
                $this->apiOutput->successFormat([], '登出成功'),
                200
            );
        } catch (\Exception $e) {
            Log::error('核銷人員登出錯誤', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(
                $this->apiOutput->exceptionFormat('登出失敗', 500, $e),
                500
            );
        }
    }

    /**
     * 取得目前核銷人員資訊
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $data = $this->verifierAccountService->me();

            return response()->json(
                $this->apiOutput->successFormat($data, '取得核銷人員資訊成功'),
                200
            );
        } catch (\Exception $e) {
            Log::error('取得核銷人員資訊錯誤', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(
                $this->apiOutput->exceptionFormat('取得核銷人員資訊失敗', 500, $e),
                500
            );
        }
    }
}
