<?php

namespace App\Http\Controllers;

use App\Services\AccountService;
use App\Formatters\ApiOutput;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    protected $accountService;
    protected $apiOutput;

    public function __construct(AccountService $accountService, ApiOutput $apiOutput)
    {
        $this->accountService = $accountService;
        $this->apiOutput = $apiOutput;
    }

    /**
     * 使用者登入
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            // 記錄登入嘗試
            Log::info('使用者登入嘗試', [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // 驗證請求參數
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255',
                'password' => 'required|string|min:6|max:255',
            ], [
                'email.required' => '電子郵件為必填欄位',
                'email.email' => '請輸入有效的電子郵件格式',
                'email.max' => '電子郵件長度不能超過255個字元',
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
            $data = $this->accountService->login($request->only(['email', 'password']));

            // 記錄登入結果
            Log::info('使用者登入成功', [
                'user_id' => $data['user']['id'],
                'email' => $data['user']['email']
            ]);

            return response()->json(
                $this->apiOutput->successFormat($data, '登入成功'),
                200
            );

        } catch (\Exception $e) {
            // 檢查是否為認證相關錯誤
            $authErrors = [
                '使用者不存在或密碼錯誤',
                '請先驗證您的電子郵件',
                '電子郵件和密碼為必填欄位'
            ];

            if (in_array($e->getMessage(), $authErrors)) {
                // 認證錯誤，返回 200 但 status 為 false
                Log::info('使用者登入認證失敗', [
                    'email' => $request->input('email'),
                    'error' => $e->getMessage()
                ]);

                return response()->json(
                    $this->apiOutput->failFormat($e->getMessage(), [], 200),
                    200
                );
            } else {
                // 真正的系統錯誤，返回 500
                Log::error('使用者登入系統錯誤', [
                    'email' => $request->input('email'),
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
     * 使用者登出
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // 記錄登出操作
            $user = auth('api')->user();
            if ($user) {
                Log::info('使用者登出', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            }

            // 呼叫 Service 處理登出邏輯
            $data = $this->accountService->logout($user);

            return response()->json(
                $this->apiOutput->successFormat($data, '登出成功'),
                200
            );

        } catch (\Exception $e) {
            Log::error('使用者登出系統錯誤', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(
                $this->apiOutput->exceptionFormat('系統錯誤，請稍後再試', 500, $e),
                500
            );
        }
    }

    /**
     * 取得目前使用者資訊
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        try {
            $user = Auth::user();

            return response()->json(
                $this->apiOutput->successFormat($user, '取得帳戶資訊成功')
            );
        } catch (\Exception $e) {
            Log::error('取得帳戶資訊失敗', [
                'error' => $e->getMessage()
            ]);
            return response()->json(
                $this->apiOutput->failFormat('取得帳戶資訊失敗', [], 500),
                500
            );
        }
    }

    /**
     * 修改密碼
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            // 記錄修改密碼操作
            $user = auth('api')->user();
            if ($user) {
                Log::info('使用者修改密碼嘗試', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            }

            // 驗證請求參數
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string|min:6|max:255',
                'new_password' => 'required|string|min:6|max:255|confirmed',
                'new_password_confirmation' => 'required|string|min:6|max:255',
            ], [
                'current_password.required' => '目前密碼為必填欄位',
                'current_password.min' => '目前密碼至少需要6個字元',
                'current_password.max' => '目前密碼長度不能超過255個字元',
                'new_password.required' => '新密碼為必填欄位',
                'new_password.min' => '新密碼至少需要6個字元',
                'new_password.max' => '新密碼長度不能超過255個字元',
                'new_password.confirmed' => '新密碼確認不一致',
                'new_password_confirmation.required' => '新密碼確認為必填欄位',
                'new_password_confirmation.min' => '新密碼確認至少需要6個字元',
                'new_password_confirmation.max' => '新密碼確認長度不能超過255個字元',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    $this->apiOutput->failFormat('驗證失敗', $validator->errors(), 422),
                    422
                );
            }

            // 呼叫 Service 處理修改密碼邏輯
            $data = $this->accountService->changePassword($user, $request->only([
                'current_password',
                'new_password'
            ]));

            // 記錄修改密碼成功
            Log::info('使用者修改密碼成功', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json(
                $this->apiOutput->successFormat($data, '密碼修改成功'),
                200
            );

        } catch (\Exception $e) {
            // 檢查是否為認證相關錯誤
            $authErrors = [
                '目前密碼錯誤',
                '新密碼不能與目前密碼相同',
                '使用者未登入'
            ];

            if (in_array($e->getMessage(), $authErrors)) {
                // 認證錯誤，返回 200 但 status 為 false
                Log::info('使用者修改密碼認證失敗', [
                    'user_id' => $user ? $user->id : null,
                    'error' => $e->getMessage()
                ]);

                return response()->json(
                    $this->apiOutput->failFormat($e->getMessage(), [], 200),
                    200
                );
            } else {
                // 真正的系統錯誤，返回 500
                Log::error('使用者修改密碼系統錯誤', [
                    'user_id' => $user ? $user->id : null,
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
}
