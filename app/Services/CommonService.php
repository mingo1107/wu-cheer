<?php
namespace App\Services;

use App\Repositories\CleanerRepository;
use App\Repositories\CustomerRepository;

class CommonService
{
    protected CleanerRepository $cleanerRepo;
    protected CustomerRepository $customerRepo;

    public function __construct(CleanerRepository $cleanerRepo, CustomerRepository $customerRepo)
    {
        $this->cleanerRepo  = $cleanerRepo;
        $this->customerRepo = $customerRepo;
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
}
