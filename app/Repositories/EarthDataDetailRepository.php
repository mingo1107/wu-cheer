<?php
namespace App\Repositories;

use App\Models\EarthDataDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EarthDataDetailRepository extends BaseRepository
{
    public function __construct(EarthDataDetail $model)
    {
        $this->model = $model;
    }

    public function generateBarcode(string $flowControlNo): string
    {
        do {
            $random  = Str::upper(Str::random(10));
            $barcode = $flowControlNo . $random;
            $exists  = $this->model->newQuery()->where('barcode', $barcode)->exists();
        } while ($exists);

        return $barcode;
    }

    public function addDetails(int $earthDataId, string $flowControlNo, int $count): int
    {
        $rows = [];
        $now  = now();
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

            if ($ids->isEmpty()) {
                return 0;
            }

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

    /**
     * 取得總數量、已印數量、未印數量
     */
    public function getTotals(int $earthDataId): array
    {
        $base     = $this->model->newQuery()->where('earth_data_id', $earthDataId);
        $total    = (clone $base)->count();
        $printed  = (clone $base)->whereNotNull('print_at')->count();
        $pending  = max(0, $total - $printed);
        return [
            'total'    => (int) $total,
            // 與前端相容：維持 verified 鍵名，但數值代表「已印數量」
            'verified' => (int) $printed,
            'pending'  => (int) $pending,
        ];
    }

    /**
     * 依日期統計每日核銷數
     */
    public function getDailyVerifiedCounts(int $earthDataId): array
    {
        $rows = $this->model->newQuery()
            ->select(DB::raw('DATE(verified_at) as day'), DB::raw('COUNT(*) as cnt'))
            ->where('earth_data_id', $earthDataId)
            ->whereNotNull('verified_at')
            ->groupBy(DB::raw('DATE(verified_at)'))
            ->orderBy(DB::raw('DATE(verified_at)'))
            ->get();

        return $rows->map(fn($r) => [
            'day'   => (string) $r->day,
            'count' => (int) $r->cnt,
        ])->all();
    }

    /**
     * 取得未列印的明細
     */
    public function getUnprintedDetails(int $earthDataId, ?int $limit = null)
    {
        $q = $this->model->newQuery()
            ->where('earth_data_id', $earthDataId)
            ->whereNull('print_at')
            ->orderBy('id');
        if (! is_null($limit) && $limit > 0) {
            $q->limit($limit);
        }
        return $q->get();
    }

    /**
     * 標記明細為已列印
     */
    public function markPrinted(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        return $this->model->newQuery()->whereIn('id', $ids)->update(['print_at' => now()]);
    }
}
