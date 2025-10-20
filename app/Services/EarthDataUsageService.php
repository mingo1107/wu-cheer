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

    public function listDetailsWithUser(int $earthDataId)
    {
        return $this->detailRepo->listDetailsWithUser($earthDataId);
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
