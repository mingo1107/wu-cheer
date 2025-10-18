<?php
namespace App\Services;

use App\Repositories\EarthDataRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EarthDataService
{
    public function __construct(private EarthDataRepository $repo)
    {
    }

    public function list(array $filters = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->repo->paginate($filters, $perPage);
    }

    public function bulkUpsert(array $rows): int
    {
        return DB::transaction(fn () => $this->repo->bulkUpsert($rows));
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(fn () => $this->repo->bulkDelete($ids));
    }
}
