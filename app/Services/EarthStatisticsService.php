<?php
namespace App\Services;

use App\Repositories\EarthDataRepository;
use App\Repositories\EarthDataDetailRepository;

class EarthStatisticsService extends BaseService
{
    public function __construct(
        private EarthDataRepository $earthRepo,
        private EarthDataDetailRepository $detailRepo,
    ) {
        parent::__construct();
    }

    public function getEarthData(int $id)
    {
        return $this->earthRepo->find($id);
    }

    public function getUsageStats(int $earthDataId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $totals = $this->detailRepo->getTotals($earthDataId, $dateFrom, $dateTo);
        $daily  = $this->detailRepo->getDailyVerifiedCounts($earthDataId, $dateFrom, $dateTo);
        return [
            'totals' => $totals,
            'daily'  => $daily,
        ];
    }
}
