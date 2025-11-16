<?php
namespace App\Services;

use App\Repositories\EarthDataRepository;
use App\Repositories\EarthDataDetailRepository;

class EarthDataUsageService
{
    public function __construct(
        private EarthDataRepository $earthRepo,
        private EarthDataDetailRepository $detailRepo,
    ) {}

    public function getEarthData(int $id)
    {
        return $this->earthRepo->find($id);
    }

    public function listDetailsWithUser(int $earthDataId, ?int $status = null)
    {
        return $this->detailRepo->listDetailsWithUser($earthDataId, $status);
    }

    public function getUsageStats(int $earthDataId): array
    {
        $totals = $this->detailRepo->getTotals($earthDataId);
        $daily  = $this->detailRepo->getDailyVerifiedCounts($earthDataId);
        return [
            'totals' => $totals,
            'daily'  => $daily,
        ];
    }

    /**
     * 更新明細狀態
     *
     * @param int $id
     * @param int $status
     * @return bool
     */
    public function updateDetailStatus(int $id, int $status): bool
    {
        return $this->detailRepo->updateStatus($id, $status);
    }

    /**
     * 取得詳細統計
     *
     * @param int $earthDataId
     * @return array
     */
    public function getDetailStats(int $earthDataId): array
    {
        return $this->detailRepo->getDetailStats($earthDataId);
    }

    /**
     * 批量回收明細
     *
     * @param int $earthDataId
     * @param int $count
     * @return int
     */
    public function recycleDetails(int $earthDataId, int $count): int
    {
        return $this->detailRepo->batchUpdateStatus(
            $earthDataId,
            $count,
            \App\Models\EarthDataDetail::STATUS_RECYCLED
        );
    }

    /**
     * 批量更新明細狀態（根據 ID 列表）
     *
     * @param int $earthDataId
     * @param array $detailIds
     * @param int $status
     * @return int
     */
    public function batchUpdateStatusByIds(int $earthDataId, array $detailIds, int $status): int
    {
        return $this->detailRepo->batchUpdateStatusByIds($earthDataId, $detailIds, $status);
    }
}
