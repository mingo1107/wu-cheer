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
            ->with('cleaners'); // 載入多對多關聯

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
            'company_id', 'batch_no', 'issue_date', 'issue_count', 'customer_id', 'valid_date_from', 'valid_date_to', 'cleaner_id', 'project_name', 'flow_control_no', 'carry_qty', 'carry_soil_type', 'status_desc', 'remark_desc', 'created_by', 'updated_by', 'sys_serial_no', 'status', 'updated_at',
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
}
