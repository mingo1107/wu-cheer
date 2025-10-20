<?php
namespace App\Services;

use App\Repositories\EarthDataRepository;
use App\Repositories\EarthDataDetailRepository;

class EarthPrintService
{
    public function __construct(
        private EarthDataRepository $earthRepo,
        private EarthDataDetailRepository $detailRepo,
    ) {}

    public function getEarthData(int $id)
    {
        return $this->earthRepo->find($id);
    }

    public function getUnprintedDetails(int $earthDataId)
    {
        return $this->detailRepo->getUnprintedDetails($earthDataId);
    }

    public function markAsPrinted(array $ids): int
    {
        return $this->detailRepo->markPrinted($ids);
    }
}
