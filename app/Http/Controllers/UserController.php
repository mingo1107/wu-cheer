<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Formatters\ApiOutput;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $userService;
    protected $apiOutput;

    public function __construct(UserService $userService, ApiOutput $apiOutput)
    {
        $this->userService = $userService;
        $this->apiOutput = $apiOutput;
    }

    /**
     * 取得使用者列表
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $data = $this->userService->getUsers($request->all());

            return response()->json(
                $this->apiOutput->successFormat($data, '取得使用者列表成功'),
                200
            );

        } catch (\Exception $e) {
            Log::error('取得使用者列表系統錯誤', [
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
     * 建立新使用者
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // 記錄建立使用者嘗試
            Log::info('建立使用者嘗試', [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // 驗證請求參數
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:6|max:255',
                'password_confirmation' => 'required|same:password',
            ], [
                'name.required' => '姓名為必填欄位',
                'name.max' => '姓名長度不能超過255個字元',
                'email.required' => '電子郵件為必填欄位',
                'email.email' => '請輸入有效的電子郵件格式',
                'email.max' => '電子郵件長度不能超過255個字元',
                'email.unique' => '此電子郵件已被使用',
                'password.required' => '密碼為必填欄位',
                'password.min' => '密碼至少需要6個字元',
                'password.max' => '密碼長度不能超過255個字元',
                'password_confirmation.required' => '確認密碼為必填欄位',
                'password_confirmation.same' => '確認密碼與密碼不一致',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    $this->apiOutput->failFormat('驗證失敗', $validator->errors(), 422),
                    422
                );
            }

            // 呼叫 Service 處理建立邏輯
            $data = $this->userService->createUser($request->only(['name', 'email', 'password']));

            // 記錄建立結果
            Log::info('使用者建立成功', [
                'user_id' => $data['user']['id'],
                'email' => $data['user']['email']
            ]);
            
            return response()->json(
                $this->apiOutput->successFormat($data, '使用者建立成功'),
                201
            );

        } catch (\Exception $e) {
            // 記錄系統錯誤
            Log::error('建立使用者系統錯誤', [
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

    /**
     * 取得特定使用者資訊
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $data = $this->userService->getUserById($id);

            return response()->json(
                $this->apiOutput->successFormat($data, '取得使用者資訊成功'),
                200
            );

        } catch (\Exception $e) {
            Log::error('取得使用者資訊系統錯誤', [
                'user_id' => $id,
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
     * 更新使用者資訊
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // 記錄更新使用者嘗試
            Log::info('更新使用者嘗試', [
                'user_id' => $id,
                'email' => $request->input('email'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // 驗證請求參數
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $id,
                'password' => 'nullable|string|min:6|max:255',
                'password_confirmation' => 'nullable|same:password',
            ], [
                'name.required' => '姓名為必填欄位',
                'name.max' => '姓名長度不能超過255個字元',
                'email.required' => '電子郵件為必填欄位',
                'email.email' => '請輸入有效的電子郵件格式',
                'email.max' => '電子郵件長度不能超過255個字元',
                'email.unique' => '此電子郵件已被其他使用者使用',
                'password.min' => '密碼至少需要6個字元',
                'password.max' => '密碼長度不能超過255個字元',
                'password_confirmation.same' => '確認密碼與密碼不一致',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    $this->apiOutput->failFormat('驗證失敗', $validator->errors(), 422),
                    422
                );
            }

            // 呼叫 Service 處理更新邏輯
            $data = $this->userService->updateUser($id, $request->only(['name', 'email', 'password']));

            // 記錄更新結果
            Log::info('使用者更新成功', [
                'user_id' => $id,
                'email' => $data['user']['email']
            ]);
            
            return response()->json(
                $this->apiOutput->successFormat($data, '使用者更新成功'),
                200
            );

        } catch (\Exception $e) {
            // 記錄系統錯誤
            Log::error('更新使用者系統錯誤', [
                'user_id' => $id,
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

    /**
     * 刪除使用者
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            // 記錄刪除使用者嘗試
            Log::info('刪除使用者嘗試', [
                'user_id' => $id
            ]);

            // 呼叫 Service 處理刪除邏輯
            $data = $this->userService->deleteUser($id);

            // 記錄刪除結果
            Log::info('使用者刪除成功', [
                'user_id' => $id
            ]);
            
            return response()->json(
                $this->apiOutput->successFormat($data, '使用者刪除成功'),
                200
            );

        } catch (\Exception $e) {
            // 記錄系統錯誤
            Log::error('刪除使用者系統錯誤', [
                'user_id' => $id,
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
