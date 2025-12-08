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

    public function addDetails(int $earthDataId, string $flowControlNo, int $count, ?string $useStartDate = null, ?string $useEndDate = null, array $meterQuantities = []): int
    {
        $rows = [];
        $now  = now();

        // 如果提供米數數量，按比例分配建立明細
        if (!empty($meterQuantities)) {
            foreach ($meterQuantities as $meterType => $qty) {
                $qty = (int) $qty;
                for ($i = 0; $i < $qty; $i++) {
                    $rows[] = [
                        'earth_data_id'  => $earthDataId,
                        'barcode'        => $this->generateBarcode($flowControlNo),
                        'use_start_date' => $useStartDate,
                        'use_end_date'   => $useEndDate,
                        'meter_type'     => (int) $meterType,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                }
            }
        } else {
            // 如果沒有米數資訊，建立指定數量的明細
            for ($i = 0; $i < $count; $i++) {
                $rows[] = [
                    'earth_data_id'  => $earthDataId,
                    'barcode'        => $this->generateBarcode($flowControlNo),
                    'use_start_date' => $useStartDate,
                    'use_end_date'   => $useEndDate,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }
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
     * 根據 barcode 取得明細
     *
     * @param string $barcode
     * @return EarthDataDetail|null
     */
    public function findByBarcode(string $barcode): ?EarthDataDetail
    {
        return $this->model->newQuery()
            ->where('barcode', $barcode)
            ->first();
    }

    /**
     * 更新明細為已核銷狀態（純資料庫操作）
     *
     * @param int $detailId
     * @param int $verifierId 核銷人員 ID
     * @param int|null $vehicleId 車輛 ID
     * @param string|null $driverName 司機名字
     * @return bool
     */
    public function markAsVerified(int $detailId, int $verifierId, ?int $vehicleId = null, ?string $driverName = null): bool
    {
        $detail = $this->model->find($detailId);
        if (!$detail) {
            return false;
        }

        $updateData = [
            'status' => EarthDataDetail::STATUS_USED,
            'verified_at' => now(),
            'verified_by' => $verifierId,
        ];

        if ($vehicleId !== null) {
            $updateData['vehicle_id'] = $vehicleId;
        }

        if ($driverName !== null) {
            $updateData['driver_name'] = $driverName;
        }

        return $detail->update($updateData);
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
            ->leftJoin('verifiers as v', 'v.id', '=', 'd.verified_by')
            ->leftJoin('cleaner_vehicles as cv', 'cv.id', '=', 'd.vehicle_id')
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
                'd.vehicle_id',
                'd.driver_name',
                DB::raw('COALESCE(u.name, v.name) as verified_by_name'),
                DB::raw('CONCAT(cv.front_plate, IF(cv.rear_plate IS NOT NULL AND cv.rear_plate != "", CONCAT(" / ", cv.rear_plate), "")) as vehicle_plate'),
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
    public function getTotals(int $earthDataId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $base = $this->model->newQuery()->where('earth_data_id', $earthDataId);

        // 日期篩選：根據 print_at 欄位
        if ($dateFrom) {
            $base->where('print_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo) {
            $base->where('print_at', '<=', $dateTo . ' 23:59:59');
        }

        $total    = (clone $base)->count();
        $printed  = (clone $base)->whereNotNull('print_at')->count();
        $pending  = max(0, $total - $printed);

        // 計算總米數：將所有 meter_type 加總
        $totalMeters = (clone $base)->sum('meter_type');

        return [
            'total'    => (int) $total,
            'total_meters' => (int) $totalMeters, // 新增總米數
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
    public function getDailyVerifiedCounts(int $earthDataId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = $this->model->newQuery()
            ->select(DB::raw('DATE(print_at) as day'), DB::raw('COUNT(*) as cnt'))
            ->where('earth_data_id', $earthDataId)
            ->whereNotNull('print_at');

        // 日期篩選
        if ($dateFrom) {
            $query->where('print_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo) {
            $query->where('print_at', '<=', $dateTo . ' 23:59:59');
        }

        $rows = $query->groupBy(DB::raw('DATE(print_at)'))
            ->orderBy(DB::raw('DATE(print_at)'))
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

    /**
     * 取得土單明細統計（依公司）
     *
     * @param int|null $companyId
     * @return array
     */
    public function getStatsByCompany(?int $companyId = null): array
    {
        $query = $this->model->newQuery()
            ->from($this->model->getTable() . ' as d')
            ->join('earth_data as e', 'e.id', '=', 'd.earth_data_id');

        if ($companyId) {
            $query->where('e.company_id', $companyId);
        }

        $total = (clone $query)->count();
        $unprinted = (clone $query)->where('d.status', \App\Models\EarthDataDetail::STATUS_UNPRINTED)->count();
        $printed = (clone $query)->where('d.status', \App\Models\EarthDataDetail::STATUS_PRINTED)->count();
        $used = (clone $query)->where('d.status', \App\Models\EarthDataDetail::STATUS_USED)->count();
        $voided = (clone $query)->where('d.status', \App\Models\EarthDataDetail::STATUS_VOIDED)->count();
        $recycled = (clone $query)->where('d.status', \App\Models\EarthDataDetail::STATUS_RECYCLED)->count();

        // 今日核銷數量
        $todayUsed = (clone $query)
            ->where('d.status', \App\Models\EarthDataDetail::STATUS_USED)
            ->whereDate('d.verified_at', today())
            ->count();

        return [
            'total' => $total,
            'unprinted' => $unprinted,
            'printed' => $printed,
            'used' => $used,
            'voided' => $voided,
            'recycled' => $recycled,
            'today_used' => $todayUsed,
        ];
    }

    /**
     * 取得最近核銷記錄
     *
     * @param int|null $companyId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentVerifies(?int $companyId = null, int $limit = 5)
    {
        $query = $this->model->newQuery()
            ->from($this->model->getTable() . ' as d')
            ->join('earth_data as e', 'e.id', '=', 'd.earth_data_id')
            ->leftJoin('verifiers as v', 'v.id', '=', 'd.verified_by')
            ->select('d.id', 'd.barcode', 'e.project_name', 'v.name as verifier_name', 'd.verified_at')
            ->where('d.status', \App\Models\EarthDataDetail::STATUS_USED)
            ->whereNotNull('d.verified_at');

        if ($companyId) {
            $query->where('e.company_id', $companyId);
        }

        return $query->orderByDesc('d.verified_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 取得本月核銷趨勢
     *
     * @param int|null $companyId
     * @param \Carbon\Carbon $startOfMonth
     * @param \Carbon\Carbon $endOfMonth
     * @return array
     */
    public function getMonthlyVerifyTrend(?int $companyId = null, $startOfMonth = null, $endOfMonth = null): array
    {
        if (!$startOfMonth) {
            $startOfMonth = now()->startOfMonth();
        }
        if (!$endOfMonth) {
            $endOfMonth = now()->endOfMonth();
        }

        $query = $this->model->newQuery()
            ->from($this->model->getTable() . ' as d')
            ->join('earth_data as e', 'e.id', '=', 'd.earth_data_id')
            ->select(
                DB::raw('DATE(d.verified_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('d.status', \App\Models\EarthDataDetail::STATUS_USED)
            ->whereNotNull('d.verified_at')
            ->whereBetween('d.verified_at', [$startOfMonth, $endOfMonth]);

        if ($companyId) {
            $query->where('e.company_id', $companyId);
        }

        $results = $query->groupBy(DB::raw('DATE(d.verified_at)'))
            ->orderBy('date')
            ->get();

        // 生成完整的月份日期列表
        $trend = [];
        $currentDate = $startOfMonth->copy();

        while ($currentDate <= $endOfMonth) {
            $dateStr = $currentDate->format('Y-m-d');
            $result = $results->firstWhere('date', $dateStr);
            $trend[] = [
                'date' => $dateStr,
                'count' => $result ? (int)$result->count : 0,
            ];
            $currentDate->addDay();
        }

        return $trend;
    }
}
