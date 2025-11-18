<?php
namespace App\Services;

use App\Models\Cleaner;
use App\Repositories\CleanerRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanerService extends BaseService
{
    protected $repo;

    public function __construct(CleanerRepository $repo)
    {
        parent::__construct();
        $this->repo = $repo;
    }

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->paginate($filters, $perPage);
    }

    public function get(int $id): ?Cleaner
    {
        return $this->repo->findById($id);
    }

    public function create(array $data): Cleaner
    {
        // tax_id is optional; if provided, ensure uniqueness within table
        if (!empty($data['tax_id']) && $this->repo->isTaxIdExists($data['tax_id'])) {
            throw new \Exception('統一編號已存在');
        }

        return DB::transaction(function () use ($data) {
            $item = $this->repo->createCleaner($data);
            Log::info('清運業者建立成功', ['id' => $item->id, 'cleaner_name' => $item->cleaner_name]);
            return $item;
        });
    }

    public function update(int $id, array $data): ?Cleaner
    {
        $item = $this->repo->findById($id);
        if (!$item) { return null; }

        if (!empty($data['tax_id']) && $this->repo->isTaxIdExists($data['tax_id'], $id)) {
            throw new \Exception('統一編號已存在');
        }

        return DB::transaction(function () use ($id, $data) {
            $ok = $this->repo->updateCleaner($id, $data);
            return $ok ? $this->repo->findById($id) : null;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(fn () => $this->repo->deleteCleaner($id));
    }
}
