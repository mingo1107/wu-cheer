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

    public function getUsageStats(int $earthDataId): array
    {
        $totals = $this->detailRepo->getTotals($earthDataId);
        $daily  = $this->detailRepo->getDailyVerifiedCounts($earthDataId);
        return [
            'totals' => $totals,
            'daily'  => $daily,
        ];
    }
}
