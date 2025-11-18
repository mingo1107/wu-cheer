<?php

namespace App\Services;

use App\Repositories\UserLogRepository;

class UserLogService extends BaseService
{
    protected $userLogRepository;

    public function __construct(UserLogRepository $userLogRepository)
    {
        parent::__construct();
        $this->userLogRepository = $userLogRepository;
    }

    /**
     * 取得使用者操作記錄列表
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getLogs(array $filters = [], int $perPage = 15)
    {
        return $this->userLogRepository->getLogs($filters, $perPage);
    }
}

