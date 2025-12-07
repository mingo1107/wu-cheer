<?php
namespace App\Services;

use App\Models\EarthData;
use App\Repositories\EarthDataDetailRepository;
use App\Repositories\EarthDataRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EarthDataService extends BaseService
{
    public function __construct(
        private EarthDataRepository $repo,
        private EarthDataDetailRepository $detailRepo,
    ) {
        parent::__construct();
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

            // 提取 cleaner_ids
            $cleanerIds = $data['cleaner_ids'] ?? [];
            unset($data['cleaner_ids']);

            $item = $this->repo->create($data);

            // 同步多對多關係
            if (!empty($cleanerIds)) {
                $item->cleaners()->sync($cleanerIds);
            }

            // 重新載入關聯
            $item->load('cleaners');

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

            // 提取 cleaner_ids
            $cleanerIds = null;
            if (isset($data['cleaner_ids'])) {
                $cleanerIds = $data['cleaner_ids'];
                unset($data['cleaner_ids']);
            }

            $updated = $this->repo->update($id, $data);
            if (! $updated) {
                throw new \Exception('更新土單資料失敗');
            }

            // 同步多對多關係（如果提供了 cleaner_ids）
            if ($cleanerIds !== null) {
                $item->cleaners()->sync($cleanerIds);
            }

            $item = $this->repo->find($id);
            $item->load('cleaners');

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

    // add/remove detail rows and update issue_count atomically
    public function adjustDetails(int $earthDataId, string $action, int $count = 0, ?string $useStartDate = null, ?string $useEndDate = null, array $meterQuantities = []): array
    {
        $earthData = $this->repo->find($earthDataId);
        if (! $earthData) {
            throw new \Exception('土單資料不存在');
        }

        // 驗證米數數量總和不超過案件的載運米數
        if (!empty($meterQuantities) && $action === 'add') {
            $totalMeter = 0;
            foreach ($meterQuantities as $meterType => $qty) {
                $totalMeter += (int) $meterType * (int) $qty;
            }

            if ($totalMeter > 0 && $earthData->carry_qty > 0) {
                if ($totalMeter > $earthData->carry_qty) {
                    throw new \Exception(sprintf(
                        '總米數(%d米)不可超過案件定義的載運米數(%d米)',
                        $totalMeter,
                        $earthData->carry_qty
                    ));
                }
            }
        }

        $affected = 0;
        DB::transaction(function () use (&$affected, $action, $count, $earthData, $useStartDate, $useEndDate, $meterQuantities) {
            if ($action === 'add') {
                $affected = $this->detailRepo->addDetails(
                    $earthData->id,
                    (string) ($earthData->flow_control_no ?? ''),
                    $count,
                    $useStartDate,
                    $useEndDate,
                    $meterQuantities
                );
                $earthData->increment('issue_count', $affected);
            } else {
                $affected = $this->detailRepo->removeDetails($earthData->id, $count);
                if ($affected > 0) {
                    $newCount               = max(0, (int) $earthData->issue_count - $affected);
                    $earthData->issue_count = $newCount;
                    $earthData->save();
                }
            }
        });

        return [
            'affected'    => $affected,
            'issue_count' => (int) $earthData->issue_count,
        ];
    }

    /**
     * 取得未列印的明細
     */
    public function getUnprintedDetails(int $earthDataId, ?int $limit = null)
    {
        try {
            return $this->detailRepo->getUnprintedDetails($earthDataId, $limit);
        } catch (\Exception $e) {
            Log::error('取得未列印明細失敗', ['error' => $e->getMessage(), 'earth_data_id' => $earthDataId]);
            throw $e;
        }
    }

    /**
     * 標記明細為已列印
     */
    public function markAsPrinted(array $ids): int
    {
        try {
            return $this->detailRepo->markPrinted($ids);
        } catch (\Exception $e) {
            Log::error('標記明細為已列印失敗', ['error' => $e->getMessage(), 'ids' => $ids]);
            throw $e;
        }
    }

    /**
     * 根據 ID 列表取得明細
     */
    public function getDetailsByIds(int $earthDataId, array $detailIds)
    {
        try {
            return $this->detailRepo->getDetailsByIds($earthDataId, $detailIds);
        } catch (\Exception $e) {
            Log::error('取得明細失敗', ['error' => $e->getMessage(), 'earth_data_id' => $earthDataId, 'ids' => $detailIds]);
            throw $e;
        }
    }

    /**
     * 結案工程（產生 PDF 證明，包含上傳的照片）
     */
    public function closeProject(int $id, array $data): EarthData
    {
        try {
            DB::beginTransaction();

            $earthData = $this->repo->find($id);
            if (!$earthData) {
                throw new \Exception('土單資料不存在');
            }

            if ($earthData->closure_status === 'closed') {
                throw new \Exception('此工程已結案');
            }

            // 處理圖片上傳
            $photoPath = null;
            if (isset($data['certificate']) && $data['certificate']) {
                $file = $data['certificate'];
                $filename = 'closure_photo_' . $earthData->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $photoPath = $file->storeAs('closure_certificates', $filename, 'public');
            }

            // 更新結案資訊
            $updateData = [
                'closure_status' => 'closed',
                'closed_at' => now(),
                'closed_by' => auth('api')->user()->name ?? 'system',
                'closure_certificate_path' => $photoPath,
                'closure_remark' => $data['closure_remark'] ?? '',
            ];

            $this->repo->update($id, $updateData);

            // 重新載入以取得更新後的資料
            $earthData->refresh();

            DB::commit();
            Log::info('工程結案成功', ['id' => $id, 'photo' => $photoPath]);

            return $earthData;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('工程結案失敗', ['error' => $e->getMessage(), 'id' => $id]);
            throw $e;
        }
    }

}
