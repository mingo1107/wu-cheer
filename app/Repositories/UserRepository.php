<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserRepository
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * 根據電子郵件查找使用者
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * 根據 ID 查找使用者
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    /**
     * 建立新使用者
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    /**
     * 更新使用者資料
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        return $this->model->where('id', $id)->update($data);
    }

    /**
     * 刪除使用者
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->model->where('id', $id)->delete();
    }

    /**
     * 驗證使用者密碼
     *
     * @param User $user
     * @param string $password
     * @return bool
     */
    public function verifyPassword(User $user, string $password): bool
    {
        return password_verify($password, $user->password);
    }

    /**
     * 取得使用者列表
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsers(array $filters = [])
    {
        $query = $this->model->query();

        // 搜尋條件
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 排序
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // 分頁
        $perPage = $filters['per_page'] ?? 15;
        if ($perPage > 0) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    /**
     * 建立新使用者
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        // 加密密碼
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        // 設定預設值
        $data['email_verified_at'] = now();

        return $this->model->create($data);
    }

    /**
     * 更新使用者
     *
     * @param int $id
     * @param array $data
     * @return User|null
     */
    public function updateUser(int $id, array $data): ?User
    {
        $user = $this->model->find($id);
        
        if (!$user) {
            return null;
        }

        // 加密密碼（如果提供）
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            // 如果沒有提供密碼，移除密碼欄位
            unset($data['password']);
        }

        $user->update($data);
        
        return $user->fresh();
    }

    /**
     * 刪除使用者
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool
    {
        $user = $this->model->find($id);
        
        if (!$user) {
            return false;
        }

        return $user->delete();
    }
}
