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

        if (Auth::guard('api')->check() && isset(Auth::guard('api')->user()->company_id)) {
            $query->where('company_id', Auth::guard('api')->user()->company_id);
        }

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('batch_no', 'like', "%{$s}%")
                    ->orWhere('project_name', 'like', "%{$s}%")
                    ->orWhere('customer_code', 'like', "%{$s}%")
                    ->orWhere('cleaner_name', 'like', "%{$s}%")
                    ->orWhere('status_desc', 'like', "%{$s}%");
            });
        }

        if (!empty($filters['issue_date_from'])) {
            $query->whereDate('issue_date', '>=', $filters['issue_date_from']);
        }
        if (!empty($filters['issue_date_to'])) {
            $query->whereDate('issue_date', '<=', $filters['issue_date_to']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

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
        $update = [
            'company_id','batch_no','doc_seq_detail','issue_date','issue_count','customer_code','valid_from','valid_to','cleaner_name','project_name','flow_control_no','carry_qty','carry_soil_type','status_desc','remark_desc','created_by','updated_by','sys_serial_no','status','updated_at'
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
}
