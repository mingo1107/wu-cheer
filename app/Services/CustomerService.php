<?php
namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerService
{
    protected $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * 取得客戶列表
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getCustomers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        try {
            return $this->customerRepository->getAllCustomers($filters, $perPage);
        } catch (\Exception $e) {
            Log::error('取得客戶列表失敗', [
                'error'   => $e->getMessage(),
                'filters' => $filters,
            ]);
            throw $e;
        }
    }

    /**
     * 取得客戶詳細資料
     *
     * @param int $id
     * @return Customer|null
     */
    public function getCustomer(int $id): ?Customer
    {
        try {
            return $this->customerRepository->getCustomerById($id);
        } catch (\Exception $e) {
            Log::error('取得客戶詳細資料失敗', [
                'error'       => $e->getMessage(),
                'customer_id' => $id,
            ]);
            throw $e;
        }
    }

    /**
     * 建立新客戶
     *
     * @param array $data
     * @return Customer
     */
    public function createCustomer(array $data): Customer
    {
        try {
            // 驗證統一編號是否重複
            if (! empty($data['tax_id']) && $this->customerRepository->isTaxIdExists($data['tax_id'])) {
                throw new \Exception('統一編號已存在');
            }

            // 驗證電子郵件是否重複
            if (! empty($data['email']) && $this->customerRepository->isEmailExists($data['email'])) {
                throw new \Exception('電子郵件已存在');
            }

            DB::beginTransaction();

            $customer = $this->customerRepository->createCustomer($data);

            DB::commit();

            Log::info('客戶建立成功', [
                'customer_id'   => $customer->id,
                'customer_name' => $customer->customer_name,
            ]);

            return $customer;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('建立客戶失敗', [
                'error' => $e->getMessage(),
                'data'  => $data,
            ]);
            throw $e;
        }
    }

    /**
     * 更新客戶資料
     *
     * @param int $id
     * @param array $data
     * @return Customer
     */
    public function updateCustomer(int $id, array $data): Customer
    {
        try {
            $customer = $this->customerRepository->getCustomerById($id);

            if (! $customer) {
                throw new \Exception('客戶不存在');
            }

            // 驗證統一編號是否重複（排除自己）
            if (! empty($data['tax_id']) && $this->customerRepository->isTaxIdExists($data['tax_id'], $id)) {
                throw new \Exception('統一編號已存在');
            }

            // 驗證電子郵件是否重複（排除自己）
            if (! empty($data['email']) && $this->customerRepository->isEmailExists($data['email'], $id)) {
                throw new \Exception('電子郵件已存在');
            }

            DB::beginTransaction();

            $updated = $this->customerRepository->updateCustomer($id, $data);

            if (! $updated) {
                throw new \Exception('更新客戶失敗');
            }

            // 重新取得更新後的客戶資料
            $customer = $this->customerRepository->getCustomerById($id);

            DB::commit();

            Log::info('客戶更新成功', [
                'customer_id'   => $id,
                'customer_name' => $customer->customer_name,
            ]);

            return $customer;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('更新客戶失敗', [
                'error'       => $e->getMessage(),
                'customer_id' => $id,
                'data'        => $data,
            ]);
            throw $e;
        }
    }

    /**
     * 刪除客戶
     *
     * @param int $id
     * @return bool
     */
    public function deleteCustomer(int $id): bool
    {
        try {
            $customer = $this->customerRepository->getCustomerById($id);

            if (! $customer) {
                throw new \Exception('客戶不存在');
            }

            DB::beginTransaction();

            $deleted = $this->customerRepository->deleteCustomer($id);

            if (! $deleted) {
                throw new \Exception('刪除客戶失敗');
            }

            DB::commit();

            Log::info('客戶刪除成功', [
                'customer_id'   => $id,
                'customer_name' => $customer->customer_name,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('刪除客戶失敗', [
                'error'       => $e->getMessage(),
                'customer_id' => $id,
            ]);
            throw $e;
        }
    }

    /**
     * 取得活躍客戶列表
     *
     * @return Collection
     */
    public function getActiveCustomers(): Collection
    {
        try {
            return $this->customerRepository->getActiveCustomers();
        } catch (\Exception $e) {
            Log::error('取得活躍客戶列表失敗', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * 搜尋客戶
     *
     * @param string $keyword
     * @return Collection
     */
    public function searchCustomers(string $keyword): Collection
    {
        try {
            return $this->customerRepository->searchByCompanyName($keyword);
        } catch (\Exception $e) {
            Log::error('搜尋客戶失敗', [
                'error'   => $e->getMessage(),
                'keyword' => $keyword,
            ]);
            throw $e;
        }
    }

    /**
     * 取得客戶統計資料
     *
     * @return array
     */
    public function getCustomerStats(): array
    {
        try {
            return $this->customerRepository->getCustomerStats();
        } catch (\Exception $e) {
            Log::error('取得客戶統計資料失敗', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * 驗證客戶資料
     *
     * @param array $data
     * @param int|null $excludeId
     * @return array
     */
    public function validateCustomerData(array $data, ?int $excludeId = null): array
    {
        $errors = [];

        // 必填欄位驗證
        if (empty($data['customer_name'])) {
            $errors['customer_name'] = '公司名稱為必填欄位';
        }

        if (empty($data['contact_person'])) {
            $errors['contact_person'] = '聯絡人為必填欄位';
        }

        // 統一編號驗證
        if (! empty($data['tax_id'])) {
            if (! preg_match('/^\d{8}$/', $data['tax_id'])) {
                $errors['tax_id'] = '統一編號格式不正確（應為8位數字）';
            } elseif ($this->customerRepository->isTaxIdExists($data['tax_id'], $excludeId)) {
                $errors['tax_id'] = '統一編號已存在';
            }
        }

        // 電子郵件驗證
        if (! empty($data['email'])) {
            if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = '電子郵件格式不正確';
            } elseif ($this->customerRepository->isEmailExists($data['email'], $excludeId)) {
                $errors['email'] = '電子郵件已存在';
            }
        }

        // 電話號碼驗證
        if (! empty($data['phone'])) {
            if (! preg_match('/^[\d\-\+\(\)\s]+$/', $data['phone'])) {
                $errors['phone'] = '電話號碼格式不正確';
            }
        }

        // 狀態驗證
        if (! empty($data['status']) && ! in_array($data['status'], ['active', 'inactive'])) {
            $errors['status'] = '狀態值不正確';
        }

        return $errors;
    }
}
