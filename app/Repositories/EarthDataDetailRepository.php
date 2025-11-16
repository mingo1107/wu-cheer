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

    public function addDetails(int $earthDataId, string $flowControlNo, int $count, ?string $useStartDate = null, ?string $useEndDate = null): int
    {
        $rows = [];
        $now  = now();
        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'earth_data_id' => $earthDataId,
                'barcode'       => $this->generateBarcode($flowControlNo),
                'use_start_date' => $useStartDate,
                'use_end_date'   => $useEndDate,
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
     *
     * @param int $earthDataId
     * @param int|null $status 狀態篩選（可選）
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listDetailsWithUser(int $earthDataId, ?int $status = null)
    {
        $query = $this->model->newQuery()
            ->from($this->model->getTable() . ' as d')
            ->leftJoin('users as u', 'u.id', '=', 'd.verified_by')
            ->where('d.earth_data_id', $earthDataId);
        
        // 狀態篩選
        if ($status !== null) {
            $query->where('d.status', $status);
        }
        
        return $query->orderByDesc('d.id')
            ->get([
                'd.id',
                'd.barcode',
                'd.status',
                'd.use_start_date',
                'd.use_end_date',
                'd.print_at',
                'd.verified_at',
                'd.verified_by',
                DB::raw('u.name as verified_by_name'),
                'd.created_at',
            ]);
    }

    /**
     * 更新明細狀態
     *
     * @param int $id
     * @param int $status
     * @return bool
     */
    public function updateStatus(int $id, int $status): bool
    {
        $detail = $this->model->find($id);
        if (! $detail) {
            return false;
        }

        return $detail->update(['status' => $status]);
    }

    /**
     * 批量更新明細狀態（根據 ID 列表）
     *
     * @param int $earthDataId
     * @param array $detailIds
     * @param int $status
     * @return int 實際更新的數量
     */
    public function batchUpdateStatusByIds(int $earthDataId, array $detailIds, int $status): int
    {
        if (empty($detailIds)) {
            return 0;
        }

        return $this->model->newQuery()
            ->where('earth_data_id', $earthDataId)
            ->whereIn('id', $detailIds)
            ->update(['status' => $status]);
    }

    /**
     * 批量更新明細的使用起訖日期
     *
     * @param int $earthDataId
     * @param array $detailIds
     * @param string|null $useStartDate
     * @param string|null $useEndDate
     * @return int
     */
    public function batchUpdateDatesByIds(int $earthDataId, array $detailIds, ?string $useStartDate = null, ?string $useEndDate = null): int
    {
        if (empty($detailIds)) {
            return 0;
        }

        $updateData = [];
        if ($useStartDate !== null) {
            $updateData['use_start_date'] = $useStartDate;
        }
        if ($useEndDate !== null) {
            $updateData['use_end_date'] = $useEndDate;
        }

        if (empty($updateData)) {
            return 0;
        }

        return $this->model->newQuery()
            ->where('earth_data_id', $earthDataId)
            ->whereIn('id', $detailIds)
            ->update($updateData);
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
     * 取得詳細統計（總數量、已列印、已核銷、作廢、回收）
     */
    public function getDetailStats(int $earthDataId): array
    {
        $base = $this->model->newQuery()->where('earth_data_id', $earthDataId);
        $total = (clone $base)->count();
        $printed = (clone $base)->where('status', \App\Models\EarthDataDetail::STATUS_PRINTED)->count();
        $used = (clone $base)->where('status', \App\Models\EarthDataDetail::STATUS_USED)->count();
        $voided = (clone $base)->where('status', \App\Models\EarthDataDetail::STATUS_VOIDED)->count();
        $recycled = (clone $base)->where('status', \App\Models\EarthDataDetail::STATUS_RECYCLED)->count();
        
        return [
            'total'    => (int) $total,
            'printed'  => (int) $printed,
            'used'     => (int) $used,
            'voided'   => (int) $voided,
            'recycled' => (int) $recycled,
        ];
    }

    /**
     * 批量更新明細狀態（回收功能）
     *
     * @param int $earthDataId
     * @param int $count 要回收的數量
     * @param int $status 目標狀態
     * @return int 實際更新的數量
     */
    public function batchUpdateStatus(int $earthDataId, int $count, int $status): int
    {
        // 取得可回收的明細（已使用狀態，且未回收）
        $details = $this->model->newQuery()
            ->where('earth_data_id', $earthDataId)
            ->where('status', \App\Models\EarthDataDetail::STATUS_USED)
            ->orderBy('id')
            ->limit($count)
            ->get();

        if ($details->isEmpty()) {
            return 0;
        }

        $ids = $details->pluck('id')->toArray();
        return $this->model->newQuery()
            ->whereIn('id', $ids)
            ->update(['status' => $status]);
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
     * 根據 ID 列表取得明細
     *
     * @param int $earthDataId
     * @param array $detailIds
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDetailsByIds(int $earthDataId, array $detailIds)
    {
        return $this->model->newQuery()
            ->where('earth_data_id', $earthDataId)
            ->whereIn('id', $detailIds)
            ->orderBy('id')
            ->get();
    }

    /**
     * 標記明細為已列印
     */
    public function markPrinted(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        return $this->model->newQuery()
            ->whereIn('id', $ids)
            ->update([
                'print_at' => now(),
                'status' => \App\Models\EarthDataDetail::STATUS_PRINTED,
            ]);
    }
}
