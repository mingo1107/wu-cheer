<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class AccountService extends BaseService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    /**
     * 使用者登入
     *
     * @param array $credentials
     * @return array
     * @throws Exception
     */
    public function login(array $credentials): array
    {
        try {
            DB::beginTransaction();

            // 驗證必要欄位
            if (empty($credentials['email']) || empty($credentials['password'])) {
                throw new Exception('電子郵件和密碼為必填欄位');
            }

            // 查找使用者
            $user = $this->userRepository->findByEmail($credentials['email']);
            if (!$user) {
                throw new Exception('使用者不存在或密碼錯誤');
            }

            // 驗證密碼
            if (!$this->userRepository->verifyPassword($user, $credentials['password'])) {
                throw new Exception('使用者不存在或密碼錯誤');
            }

            // 檢查使用者是否已驗證電子郵件
            if (!$user->email_verified_at) {
                throw new Exception('請先驗證您的電子郵件');
            }

            // 生成 JWT token
            $token = auth('api')->login($user);

            DB::commit();

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'role' => $user->role ?? 1, // 預設為一般使用者
                ],
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60, // 轉換為秒
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 使用者登出
     *
     * @param User $user
     * @return array
     */
    public function logout(User $user): array
    {
        try {
            // 使用 JWT 登出
            auth('api')->logout();

            return [
                'message' => '登出成功'
            ];
        } catch (Exception $e) {
            throw new Exception('登出失敗: ' . $e->getMessage());
        }
    }

    /**
     * 取得目前使用者資訊
     *
     * @return array
     */
    public function getCurrentUser(): array
    {
        $user = auth('api')->user();
        
        if (!$user) {
            throw new Exception('使用者未登入');
        }

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'role' => $user->role ?? 1, // 預設為一般使用者
            ]
        ];
    }

    /**
     * 修改密碼
     *
     * @param User $user
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function changePassword(User $user, array $data): array
    {
        try {
            DB::beginTransaction();

            // 驗證使用者是否已登入
            if (!$user) {
                throw new Exception('使用者未登入');
            }

            // 驗證目前密碼
            if (!$this->userRepository->verifyPassword($user, $data['current_password'])) {
                throw new Exception('目前密碼錯誤');
            }

            // 檢查新密碼是否與目前密碼相同
            if ($this->userRepository->verifyPassword($user, $data['new_password'])) {
                throw new Exception('新密碼不能與目前密碼相同');
            }

            // 更新密碼
            $this->userRepository->updatePassword($user->id, $data['new_password']);

            DB::commit();

            return [
                'message' => '密碼修改成功'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
