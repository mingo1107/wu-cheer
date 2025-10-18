<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRepository extends BaseRepository
{
    protected $model;

    public function __construct(Customer $model)
    {
        $this->model = $model;
    }

    /**
     * 取得所有客戶資料
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllCustomers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // 搜尋功能
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('tax_id', 'like', "%{$search}%");
            });
        }

        // 狀態篩選
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // 排序
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * 根據 ID 取得客戶資料
     *
     * @param int $id
     * @return Customer|null
     */
    public function getCustomerById(int $id): ?Customer
    {
        return $this->model->find($id);
    }

    /**
     * 建立新客戶
     *
     * @param array $data
     * @return Customer
     */
    public function createCustomer(array $data): Customer
    {
        return $this->model->create($data);
    }

    /**
     * 更新客戶資料
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateCustomer(int $id, array $data): bool
    {
        $customer = $this->getCustomerById($id);
        
        if (!$customer) {
            return false;
        }

        return $customer->update($data);
    }

    /**
     * 刪除客戶
     *
     * @param int $id
     * @return bool
     */
    public function deleteCustomer(int $id): bool
    {
        $customer = $this->getCustomerById($id);
        
        if (!$customer) {
            return false;
        }

        return $customer->delete();
    }

    /**
     * 取得活躍客戶列表
     *
     * @return Collection
     */
    public function getActiveCustomers(): Collection
    {
        return $this->model->active()->orderBy('company_name')->get();
    }

    /**
     * 根據公司名稱搜尋客戶
     *
     * @param string $companyName
     * @return Collection
     */
    public function searchByCompanyName(string $companyName): Collection
    {
        return $this->model
            ->where('company_name', 'like', "%{$companyName}%")
            ->orderBy('company_name')
            ->get();
    }

    /**
     * 檢查統一編號是否已存在
     *
     * @param string $taxId
     * @param int|null $excludeId
     * @return bool
     */
    public function isTaxIdExists(string $taxId, ?int $excludeId = null): bool
    {
        $query = $this->model->where('tax_id', $taxId);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * 檢查電子郵件是否已存在
     *
     * @param string $email
     * @param int|null $excludeId
     * @return bool
     */
    public function isEmailExists(string $email, ?int $excludeId = null): bool
    {
        $query = $this->model->where('email', $email);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * 取得客戶統計資料
     *
     * @return array
     */
    public function getCustomerStats(): array
    {
        return [
            'total' => $this->model->count(),
            'active' => $this->model->active()->count(),
            'inactive' => $this->model->inactive()->count(),
        ];
    }
}

