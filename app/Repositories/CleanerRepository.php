<?php
namespace App\Repositories;

use App\Models\Cleaner;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CleanerRepository extends BaseRepository
{
    public function __construct(Cleaner $model)
    {
        $this->model = $model;
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
            $query->where('company_id', auth('api')->user()->company_id);
        }

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('cleaner_name', 'like', "%{$s}%")
                  ->orWhere('contact_person', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('tax_id', 'like', "%{$s}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Cleaner
    {
        $query = $this->model->newQuery();
        if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
            $query->where('company_id', auth('api')->user()->company_id);
        }
        return $query->find($id);
    }

    public function createCleaner(array $data): Cleaner
    {
        if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
            $data['company_id'] = $data['company_id'] ?? auth('api')->user()->company_id;
        }
        return $this->model->create($data);
    }

    public function updateCleaner(int $id, array $data): bool
    {
        $item = $this->findById($id);
        if (!$item) return false;
        return $item->update($data);
    }

    public function deleteCleaner(int $id): bool
    {
        $item = $this->findById($id);
        if (!$item) return false;
        return (bool) $item->delete();
    }

    public function isTaxIdExists(string $taxId, ?int $excludeId = null): bool
    {
        $query = $this->model->where('tax_id', $taxId);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}
