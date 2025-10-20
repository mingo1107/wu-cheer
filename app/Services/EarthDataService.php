<?php
namespace App\Services;

use App\Models\EarthData;
use App\Repositories\EarthDataRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EarthDataService
{
    public function __construct(private EarthDataRepository $repo)
    {
    }

    public function getEarthDataList(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        try {
            return $this->repo->paginate($filters, $perPage);
        } catch (\Exception $e) {
            Log::error('取得土單資料列表失敗', ['error' => $e->getMessage(), 'filters' => $filters]);
            throw $e;
        }
    }

    public function getEarthData(int $id): ?EarthData
    {
        try {
            return $this->repo->find($id);
        } catch (\Exception $e) {
            Log::error('取得土單資料失敗', ['error' => $e->getMessage(), 'id' => $id]);
            throw $e;
        }
    }

    public function createEarthData(array $data): EarthData
    {
        try {
            DB::beginTransaction();
            $item = $this->repo->create($data);
            DB::commit();
            Log::info('土單資料建立成功', ['id' => $item->id]);
            return $item;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('建立土單資料失敗', ['error' => $e->getMessage(), 'data' => $data]);
            throw $e;
        }
    }

    public function updateEarthData(int $id, array $data): EarthData
    {
        try {
            $item = $this->repo->find($id);
            if (! $item) {
                throw new \Exception('土單資料不存在');
            }

            DB::beginTransaction();
            $updated = $this->repo->update($id, $data);
            if (! $updated) {
                throw new \Exception('更新土單資料失敗');
            }
            $item = $this->repo->find($id);
            DB::commit();

            Log::info('土單資料更新成功', ['id' => $id]);
            return $item;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('更新土單資料失敗', ['error' => $e->getMessage(), 'id' => $id, 'data' => $data]);
            throw $e;
        }
    }

    public function deleteEarthData(int $id): bool
    {
        try {
            $item = $this->repo->find($id);
            if (! $item) {
                return false;
            }
            DB::beginTransaction();
            $deleted = $this->repo->delete($id);
            DB::commit();
            Log::info('土單資料刪除成功', ['id' => $id]);
            return (bool) $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('刪除土單資料失敗', ['error' => $e->getMessage(), 'id' => $id]);
            throw $e;
        }
    }
}
