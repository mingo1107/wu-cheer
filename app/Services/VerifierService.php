<?php
namespace App\Services;

use App\Models\Verifier;
use App\Repositories\VerifierRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerifierService extends BaseService
{
    protected $verifierRepository;

    public function __construct(VerifierRepository $verifierRepository)
    {
        parent::__construct();
        $this->verifierRepository = $verifierRepository;
    }

    /**
     * 取得核銷人員列表
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getVerifiers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        try {
            return $this->verifierRepository->getAllVerifiers($filters, $perPage);
        } catch (\Exception $e) {
            Log::error('取得核銷人員列表失敗', [
                'error'   => $e->getMessage(),
                'filters' => $filters,
            ]);
            throw $e;
        }
    }

    /**
     * 取得核銷人員詳細資料
     *
     * @param int $id
     * @return Verifier|null
     */
    public function getVerifier(int $id): ?Verifier
    {
        try {
            return $this->verifierRepository->getVerifierById($id);
        } catch (\Exception $e) {
            Log::error('取得核銷人員詳細資料失敗', [
                'error'       => $e->getMessage(),
                'verifier_id' => $id,
            ]);
            throw $e;
        }
    }

    /**
     * 建立新核銷人員
     *
     * @param array $data
     * @return Verifier
     */
    public function createVerifier(array $data): Verifier
    {
        try {
            // 驗證帳號是否重複
            if (empty($data['account'])) {
                throw new \Exception('帳號為必填欄位');
            }

            if ($this->verifierRepository->isAccountExists($data['account'])) {
                throw new \Exception('帳號已存在');
            }

            // 驗證密碼
            if (empty($data['password'])) {
                throw new \Exception('密碼為必填欄位');
            }

            if (strlen($data['password']) < 6) {
                throw new \Exception('密碼長度至少需要6個字元');
            }

            DB::beginTransaction();

            $verifier = $this->verifierRepository->createVerifier($data);

            DB::commit();

            Log::info('核銷人員建立成功', [
                'verifier_id' => $verifier->id,
                'account'     => $verifier->account,
            ]);

            return $verifier;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('建立核銷人員失敗', [
                'error' => $e->getMessage(),
                'data'  => $data,
            ]);
            throw $e;
        }
    }

    /**
     * 更新核銷人員資料
     *
     * @param int $id
     * @param array $data
     * @return Verifier
     */
    public function updateVerifier(int $id, array $data): Verifier
    {
        try {
            $verifier = $this->verifierRepository->getVerifierById($id);

            if (! $verifier) {
                throw new \Exception('核銷人員不存在');
            }

            // 驗證帳號是否重複（排除自己）
            if (! empty($data['account']) && $this->verifierRepository->isAccountExists($data['account'], $id)) {
                throw new \Exception('帳號已存在');
            }

            // 如果更新密碼，驗證密碼長度
            if (! empty($data['password']) && strlen($data['password']) < 6) {
                throw new \Exception('密碼長度至少需要6個字元');
            }

            // 如果沒有提供密碼，移除密碼欄位
            if (empty($data['password'])) {
                unset($data['password']);
            }

            DB::beginTransaction();

            $updated = $this->verifierRepository->updateVerifier($id, $data);

            if (! $updated) {
                throw new \Exception('更新核銷人員失敗');
            }

            // 重新取得更新後的核銷人員資料
            $verifier = $this->verifierRepository->getVerifierById($id);

            DB::commit();

            Log::info('核銷人員更新成功', [
                'verifier_id' => $id,
                'account'     => $verifier->account,
            ]);

            return $verifier;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('更新核銷人員失敗', [
                'error'       => $e->getMessage(),
                'verifier_id' => $id,
                'data'        => $data,
            ]);
            throw $e;
        }
    }

    /**
     * 刪除核銷人員
     *
     * @param int $id
     * @return bool
     */
    public function deleteVerifier(int $id): bool
    {
        try {
            $verifier = $this->verifierRepository->getVerifierById($id);

            if (! $verifier) {
                throw new \Exception('核銷人員不存在');
            }

            DB::beginTransaction();

            $deleted = $this->verifierRepository->deleteVerifier($id);

            if (! $deleted) {
                throw new \Exception('刪除核銷人員失敗');
            }

            DB::commit();

            Log::info('核銷人員刪除成功', [
                'verifier_id' => $id,
                'account'    => $verifier->account,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('刪除核銷人員失敗', [
                'error'       => $e->getMessage(),
                'verifier_id' => $id,
            ]);
            throw $e;
        }
    }

    /**
     * 取得活躍核銷人員列表
     *
     * @return Collection
     */
    public function getActiveVerifiers(): Collection
    {
        try {
            return $this->verifierRepository->getActiveVerifiers();
        } catch (\Exception $e) {
            Log::error('取得活躍核銷人員列表失敗', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * 取得核銷人員統計資料
     *
     * @return array
     */
    public function getVerifierStats(): array
    {
        try {
            return $this->verifierRepository->getVerifierStats();
        } catch (\Exception $e) {
            Log::error('取得核銷人員統計資料失敗', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * 驗證核銷人員資料
     *
     * @param array $data
     * @param int|null $excludeId
     * @return array
     */
    public function validateVerifierData(array $data, ?int $excludeId = null): array
    {
        $errors = [];

        // 必填欄位驗證 - 名字
        if (empty($data['name'])) {
            $errors['name'] = '名字為必填欄位';
        }

        // 必填欄位驗證 - 帳號
        if (empty($data['account'])) {
            $errors['account'] = '帳號為必填欄位';
        } elseif ($this->verifierRepository->isAccountExists($data['account'], $excludeId)) {
            $errors['account'] = '帳號已存在';
        }

        // Email 驗證（選填）
        if (! empty($data['email'])) {
            if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = '電子郵件格式不正確';
            }
        }

        // 電話驗證（選填）
        if (! empty($data['phone'])) {
            if (! preg_match('/^[\d\-\+\(\)\s]+$/', $data['phone'])) {
                $errors['phone'] = '電話號碼格式不正確';
            }
        }

        // 密碼驗證（建立時必填，更新時選填）
        if (empty($excludeId)) {
            // 建立時
            if (empty($data['password'])) {
                $errors['password'] = '密碼為必填欄位';
            } elseif (strlen($data['password']) < 6) {
                $errors['password'] = '密碼長度至少需要6個字元';
            }
        } else {
            // 更新時
            if (! empty($data['password']) && strlen($data['password']) < 6) {
                $errors['password'] = '密碼長度至少需要6個字元';
            }
        }

        // 狀態驗證
        if (! empty($data['status']) && ! in_array($data['status'], ['active', 'inactive'])) {
            $errors['status'] = '狀態值不正確';
        }

        return $errors;
    }
}

