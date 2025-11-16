<?php

namespace App\Services\VerifierPlatform;

use App\Models\Verifier;
use App\Repositories\VerifierRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class VerifierAccountService
{
    protected $verifierRepository;

    public function __construct(VerifierRepository $verifierRepository)
    {
        $this->verifierRepository = $verifierRepository;
    }

    /**
     * 核銷人員登入
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
            if (empty($credentials['account']) || empty($credentials['password'])) {
                throw new Exception('帳號和密碼為必填欄位');
            }

            // 查找核銷人員
            $verifier = $this->verifierRepository->findByAccount($credentials['account']);
            if (!$verifier) {
                throw new Exception('帳號不存在或密碼錯誤');
            }

            // 驗證密碼
            if (!$verifier->verifyPassword($credentials['password'])) {
                throw new Exception('帳號不存在或密碼錯誤');
            }

            // 檢查狀態
            if ($verifier->status !== 'active') {
                throw new Exception('帳號已被停用');
            }

            // 更新最後登入資訊
            $verifier->update([
                'last_login_ip' => request()->ip(),
                'last_login_at' => now(),
            ]);

            // 生成 JWT token
            $token = auth('verifier')->login($verifier);

            DB::commit();

            return [
                'verifier' => [
                    'id' => $verifier->id,
                    'name' => $verifier->name,
                    'account' => $verifier->account,
                    'email' => $verifier->email,
                    'phone' => $verifier->phone,
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
     * 取得目前核銷人員資訊
     *
     * @return array
     */
    public function me(): array
    {
        $verifier = auth('verifier')->user();
        
        if (!$verifier) {
            throw new Exception('未登入');
        }

        return [
            'verifier' => [
                'id' => $verifier->id,
                'name' => $verifier->name,
                'account' => $verifier->account,
                'email' => $verifier->email,
                'phone' => $verifier->phone,
                'status' => $verifier->status,
                'last_login_ip' => $verifier->last_login_ip,
                'last_login_at' => $verifier->last_login_at,
            ],
        ];
    }

    /**
     * 核銷人員登出
     *
     * @return bool
     */
    public function logout(): bool
    {
        auth('verifier')->logout();
        return true;
    }
}

