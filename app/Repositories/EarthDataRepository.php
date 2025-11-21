<?php

namespace App\Repositories;

use App\Models\EarthData;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class EarthDataRepository extends BaseRepository
{
    public function __construct(EarthData $model)
    {
        $this->model = $model;
    }

    public function paginate(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = $this->model->newQuery();
        $table = $this->model->getTable();

        // join users to resolve created_by/updated_by names, and customers to resolve display names
        // cleaners 改為多對多關係，使用 with 載入
        $query->leftJoin('users as cu', 'cu.id', '=', $table . '.created_by')
            ->leftJoin('users as uu', 'uu.id', '=', $table . '.updated_by')
            ->leftJoin('customers as cust', 'cust.id', '=', $table . '.customer_id')
            ->select(
                $table . '.*',
                'cu.name as created_by_name',
                'uu.name as updated_by_name',
                'cust.customer_name as customer_name'
            )
            ->with('cleaners') // 載入多對多關聯
            ->withCount([
                'details as voided_count' => function ($q) {
                    $q->where('status', \App\Models\EarthDataDetail::STATUS_VOIDED);
                },
                'details as recycled_count' => function ($q) {
                    $q->where('status', \App\Models\EarthDataDetail::STATUS_RECYCLED);
                }
            ]);

        if (Auth::guard('api')->check() && isset(Auth::guard('api')->user()->company_id)) {
            $query->where($table . '.company_id', Auth::guard('api')->user()->company_id);
        }

        if (! empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s, $table) {
                $q->where($table . '.batch_no', 'like', "%{$s}%")
                    ->orWhere($table . '.project_name', 'like', "%{$s}%")
                    ->orWhere('cust.customer_name', 'like', "%{$s}%")
                    ->orWhere($table . '.status_desc', 'like', "%{$s}%");
            });
        }

        if (! empty($filters['issue_date_from'])) {
            $query->whereDate('issue_date', '>=', $filters['issue_date_from']);
        }
        if (! empty($filters['issue_date_to'])) {
            $query->whereDate('issue_date', '<=', $filters['issue_date_to']);
        }

        $sortBy    = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        // Qualify base table columns to avoid ambiguity
        $qualifiedSort = str_contains($sortBy, '.') ? $sortBy : ($table . '.' . $sortBy);
        $query->orderBy($qualifiedSort, $sortOrder);

        return $query->paginate($perPage);
    }

    public function bulkUpsert(array $rows): int
    {
        if (Auth::guard('api')->check() && isset(Auth::guard('api')->user()->company_id)) {
            $companyId = Auth::guard('api')->user()->company_id;
            foreach ($rows as &$r) {
                $r['company_id'] = $r['company_id'] ?? $companyId;
            }
            unset($r);
        }

        $now = now();
        foreach ($rows as &$r) {
            $r['updated_at'] = $now;
            if (empty($r['id'])) {
                $r['created_at'] = $now;
            }
        }
        unset($r);

        $uniqueBy = ['id'];
        $update   = [
            'company_id',
            'batch_no',
            'issue_date',
            'issue_count',
            'customer_id',
            'valid_date_from',
            'valid_date_to',
            'cleaner_id',
            'project_name',
            'flow_control_no',
            'carry_qty',
            'carry_soil_type',
            'status_desc',
            'remark_desc',
            'created_by',
            'updated_by',
            'sys_serial_no',
            'status',
            'updated_at',
        ];
        return $this->model->upsert($rows, $uniqueBy, $update);
    }

    public function bulkDelete(array $ids): int
    {
        $query = $this->model->newQuery();
        if (Auth::guard('api')->check() && isset(Auth::guard('api')->user()->company_id)) {
            $query->where('company_id', Auth::guard('api')->user()->company_id);
        }
        return $query->whereIn('id', $ids)->delete();
    }

    /**
     * 覆寫 find 方法以載入 cleaners 關聯
     */
    public function find($id, $columns = ['*'])
    {
        return $this->model->with('cleaners')->find($id, $columns);
    }

    /**
     * 取得土單工程 datalist（可搜尋、狀態過濾）
     *
     * @param string $status 狀態篩選: all|active|inactive
     * @param string $q 關鍵字（批號/工程/客戶）
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDatalist(string $status = 'all', string $q = ''): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery()
            ->from('earth_data')
            ->leftJoin('customers as c', 'c.id', '=', 'earth_data.customer_id')
            ->select('earth_data.id', 'earth_data.batch_no', 'earth_data.project_name', 'earth_data.status', \DB::raw('c.customer_name as customer_name'));

        // 公司範圍
        if (Auth::guard('api')->check() && isset(Auth::guard('api')->user()->company_id)) {
            $query->where('earth_data.company_id', Auth::guard('api')->user()->company_id);
        }

        // 狀態篩選
        if (in_array($status, ['active', 'inactive'], true)) {
            $query->where('earth_data.status', $status);
        }

        // 關鍵字搜尋
        $q = trim($q);
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('earth_data.batch_no', 'like', "%{$q}%")
                    ->orWhere('earth_data.project_name', 'like', "%{$q}%")
                    ->orWhere('c.customer_name', 'like', "%{$q}%");
            });
        }

        return $query->orderByDesc('earth_data.created_at')->limit(300)->get();
    }

    /**
     * 取得土單統計（依公司）
     *
     * @param int|null $companyId
     * @return array
     */
    public function getStats(?int $companyId = null): array
    {
        $query = $this->model->newQuery();

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $total = (clone $query)->count();
        $active = (clone $query)->where('status', 'active')->count();
        $inactive = (clone $query)->where('status', 'inactive')->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
        ];
    }

    /**
     * 取得最近建立的土單
     *
     * @param int|null $companyId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentEarthData(?int $companyId = null, int $limit = 5)
    {
        $query = $this->model->newQuery()
            ->leftJoin('customers as c', 'c.id', '=', 'earth_data.customer_id')
            ->select('earth_data.id', 'earth_data.batch_no', 'earth_data.project_name', 'c.customer_name', 'earth_data.created_at');

        if ($companyId) {
            $query->where('earth_data.company_id', $companyId);
        }

        return $query->orderByDesc('earth_data.created_at')
            ->limit($limit)
            ->get();
    }
}
