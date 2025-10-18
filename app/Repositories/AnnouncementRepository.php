<?php

namespace App\Repositories;

use App\Models\Announcement;
use Illuminate\Pagination\LengthAwarePaginator;

class AnnouncementRepository extends BaseRepository
{
    protected $model;

    public function __construct(Announcement $model)
    {
        $this->model = $model;
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // 公司範圍
        if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
            $query->where('company_id', auth('api')->user()->company_id);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool)$filters['is_active']);
        }

        if (!empty($filters['starts_from'])) {
            $query->whereDate('starts_at', '>=', $filters['starts_from']);
        }
        if (!empty($filters['ends_to'])) {
            $query->whereDate('ends_at', '<=', $filters['ends_to']);
        }

        $sortBy = $filters['sort_by'] ?? 'starts_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }
    
    // Note: BaseRepository already implements find/create/update/delete with signatures:
    // find($id, $columns=['*']), create($data, array $options=[]), update($id, array $params, array $options=[]), delete($id, $force=false)
}
