<?php
namespace App\Repositories;

use App\Models\EarthDataDetail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class EarthDataDetailRepository extends BaseRepository
{
    public function __construct(EarthDataDetail $model)
    {
        $this->model = $model;
    }

    public function generateBarcode(string $flowControlNo): string
    {
        do {
            $random = Str::upper(Str::random(10));
            $barcode = $flowControlNo . $random;
            $exists = $this->model->newQuery()->where('barcode', $barcode)->exists();
        } while ($exists);

        return $barcode;
    }

    public function addDetails(int $earthDataId, string $flowControlNo, int $count): int
    {
        $rows = [];
        $now = now();
        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'earth_data_id' => $earthDataId,
                'barcode'       => $this->generateBarcode($flowControlNo),
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }
        return $this->model->insert($rows) ? count($rows) : 0;
    }

    public function removeDetails(int $earthDataId, int $count): int
    {
        // Remove the most recent, unverified details first
        return DB::transaction(function () use ($earthDataId, $count) {
            $ids = $this->model->newQuery()
                ->where('earth_data_id', $earthDataId)
                ->whereNull('verified_at')
                ->orderByDesc('id')
                ->limit($count)
                ->pluck('id');

            if ($ids->isEmpty()) return 0;

            return $this->model->newQuery()->whereIn('id', $ids)->delete();
        });
    }

    /**
     * 取得指定工程的使用明細（含核銷人員姓名）
     */
    public function listDetailsWithUser(int $earthDataId)
    {
        return $this->model->newQuery()
            ->from($this->model->getTable() . ' as d')
            ->leftJoin('users as u', 'u.id', '=', 'd.verified_by')
            ->where('d.earth_data_id', $earthDataId)
            ->orderByDesc('d.id')
            ->get([
                'd.id',
                'd.barcode',
                'd.print_at',
                'd.verified_at',
                'd.verified_by',
                DB::raw('u.name as verified_by_name'),
                'd.created_at',
            ]);
    }
}
