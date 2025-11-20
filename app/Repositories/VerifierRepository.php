<?php
namespace App\Repositories;

use App\Models\Verifier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class VerifierRepository extends BaseRepository
{
    protected $model;

    public function __construct(Verifier $model)
    {
        $this->model = $model;
    }

    /**
     * 取得所有核銷人員資料
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllVerifiers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // 公司範圍
        if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
            $query->where('company_id', auth('api')->user()->company_id);
        }

        // 搜尋功能
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('account', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // 狀態篩選
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // 排序
        $sortBy    = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * 根據 ID 取得核銷人員資料
     *
     * @param int $id
     * @return Verifier|null
     */
    public function getVerifierById(int $id): ?Verifier
    {
        $query = $this->model->newQuery();
        if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
            $query->where('company_id', auth('api')->user()->company_id);
        }
        return $query->find($id);
    }

    /**
     * 根據帳號取得核銷人員資料（登入用，不檢查 company_id）
     *
     * @param string $account
     * @return Verifier|null
     */
    public function findByAccount(string $account): ?Verifier
    {
        return $this->model->newQuery()
            ->where('account', $account)
            ->first();
    }

    /**
     * 建立新核銷人員
     *
     * @param array $data
     * @return Verifier
     */
    public function createVerifier(array $data): Verifier
    {
        if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
            $data['company_id'] = $data['company_id'] ?? auth('api')->user()->company_id;
        }
        return $this->model->create($data);
    }

    /**
     * 更新核銷人員資料
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateVerifier(int $id, array $data): bool
    {
        $verifier = $this->getVerifierById($id);

        if (! $verifier) {
            return false;
        }

        return $verifier->update($data);
    }

    /**
     * 刪除核銷人員
     *
     * @param int $id
     * @return bool
     */
    public function deleteVerifier(int $id): bool
    {
        $verifier = $this->getVerifierById($id);

        if (! $verifier) {
            return false;
        }

        return $verifier->delete();
    }

    /**
     * 取得活躍核銷人員列表
     *
     * @return Collection
     */
    public function getActiveVerifiers(): Collection
    {
        $query = $this->model->active();
        if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
            $query->where('company_id', auth('api')->user()->company_id);
        }
        return $query->orderBy('account')->get();
    }

    /**
     * 檢查帳號是否已存在
     *
     * @param string $account
     * @param int|null $excludeId
     * @return bool
     */
    public function isAccountExists(string $account, ?int $excludeId = null): bool
    {
        $query = $this->model->where('account', $account);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // 公司範圍
        if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
            $query->where('company_id', auth('api')->user()->company_id);
        }

        return $query->exists();
    }

    /**
     * 更新最後登入資訊
     *
     * @param int $id
     * @param string $ip
     * @return bool
     */
    public function updateLastLogin(int $id, string $ip): bool
    {
        $verifier = $this->getVerifierById($id);
        if (! $verifier) {
            return false;
        }

        return $verifier->update([
            'last_login_ip' => $ip,
            'last_login_at' => now(),
        ]);
    }

    /**
     * 取得核銷人員統計資料
     *
     * @return array
     */
    public function getVerifierStats(): array
    {
        $query = $this->model->newQuery();
        if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
            $query->where('company_id', auth('api')->user()->company_id);
        }

        return [
            'total'    => $query->count(),
            'active'   => (clone $query)->active()->count(),
            'inactive' => (clone $query)->inactive()->count(),
        ];
    }

    /**
     * 取得活躍核銷人員數量（依公司）
     *
     * @param int|null $companyId
     * @return int
     */
    public function getActiveCountByCompany(?int $companyId = null): int
    {
        $query = $this->model->newQuery()
            ->where('status', 'active');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->count();
    }
}

