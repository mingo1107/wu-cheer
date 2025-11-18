<?php

namespace App\Repositories;

use App\Models\UserLog;

class UserLogRepository extends BaseRepository
{
    protected $model;

    public function __construct(UserLog $model)
    {
        $this->model = $model;
    }

    /**
     * 取得使用者操作記錄列表
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getLogs(array $filters = [], int $perPage = 15)
    {
        $query = $this->model->with('user');

        // 公司範圍
        if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
            $query->where('company_id', auth('api')->user()->company_id);
        }

        // 使用者篩選
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // 控制器篩選
        if (!empty($filters['controller'])) {
            $query->where('controller', $filters['controller']);
        }

        // 方法篩選
        if (!empty($filters['method'])) {
            $query->where('method', $filters['method']);
        }

        // 結果篩選
        if (isset($filters['result']) && $filters['result'] !== '') {
            $query->where('result', $filters['result']);
        }

        // 日期範圍篩選
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // 排序
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }
}
