<?php
namespace App\Services;

use App\Repositories\CleanerRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\EarthDataRepository;

class CommonService extends BaseService
{
    protected CleanerRepository $cleanerRepo;
    protected CustomerRepository $customerRepo;
    protected EarthDataRepository $earthDataRepo;

    public function __construct(
        CleanerRepository $cleanerRepo,
        CustomerRepository $customerRepo,
        EarthDataRepository $earthDataRepo
    ) {
        parent::__construct();
        $this->cleanerRepo  = $cleanerRepo;
        $this->customerRepo = $customerRepo;
        $this->earthDataRepo = $earthDataRepo;
    }

    public function getCleanerList(): array
    {
        $wheres = [];
        if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
            $wheres = ['company_id' => auth('api')->user()->company_id];
        }
        $items = $this->cleanerRepo->all([
            'selects' => ['id', 'cleaner_name'],
            'wheres'  => $wheres,
            'sort_by' => 'cleaner_name',
        ]);
        return $items->map(fn($row) => [
            'id' => $row->id,
            'name' => $row->cleaner_name,
        ])->toArray();
    }

    public function getCustomerList(): array
    {
        $wheres = [];
        if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
            $wheres = ['company_id' => auth('api')->user()->company_id];
        }
        $items = $this->customerRepo->all([
            'selects' => ['id', 'customer_name'],
            'wheres'  => $wheres,
            'sort_by' => 'customer_name',
        ]);
        return $items->map(fn($row) => [
            'id' => $row->id,
            'name' => $row->customer_name,
        ])->toArray();
    }

    /**
     * 取得土單工程 datalist（可搜尋、狀態過濾）
     *
     * @param string $status 狀態篩選: all|active|inactive
     * @param string $q 關鍵字（批號/工程/客戶）
     * @return array
     */
    public function getEarthDataDatalist(string $status = 'all', string $q = ''): array
    {
        $data = $this->earthDataRepo->getDatalist($status, $q);

        return $data->map(function ($r) {
            $text = trim(($r->batch_no ?: '') . ' - ' . ($r->project_name ?: ''));
            if (!empty($r->customer_name)) {
                $text .= ' (' . $r->customer_name . ')';
            }
            return [
                'id' => $r->id,
                'text' => $text,
                'batch_no' => $r->batch_no,
                'project_name' => $r->project_name,
                'customer_name' => $r->customer_name,
                'status' => $r->status,
            ];
        })->toArray();
    }

    /**
     * 取得土單明細狀態列表
     *
     * @return array
     */
    public function getEarthDataDetailStatusList(): array
    {
        return [
            [
                'value' => null,
                'label' => '全部',
            ],
            [
                'value' => \App\Models\EarthDataDetail::STATUS_UNPRINTED,
                'label' => \App\Models\EarthDataDetail::STATUS_LABELS[\App\Models\EarthDataDetail::STATUS_UNPRINTED],
            ],
            [
                'value' => \App\Models\EarthDataDetail::STATUS_PRINTED,
                'label' => \App\Models\EarthDataDetail::STATUS_LABELS[\App\Models\EarthDataDetail::STATUS_PRINTED],
            ],
            [
                'value' => \App\Models\EarthDataDetail::STATUS_USED,
                'label' => \App\Models\EarthDataDetail::STATUS_LABELS[\App\Models\EarthDataDetail::STATUS_USED],
            ],
            [
                'value' => \App\Models\EarthDataDetail::STATUS_VOIDED,
                'label' => \App\Models\EarthDataDetail::STATUS_LABELS[\App\Models\EarthDataDetail::STATUS_VOIDED],
            ],
            [
                'value' => \App\Models\EarthDataDetail::STATUS_RECYCLED,
                'label' => \App\Models\EarthDataDetail::STATUS_LABELS[\App\Models\EarthDataDetail::STATUS_RECYCLED],
            ],
        ];
    }
}
