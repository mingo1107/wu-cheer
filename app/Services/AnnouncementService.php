<?php

namespace App\Services;

use App\Repositories\AnnouncementRepository;
use Illuminate\Support\Facades\DB;

class AnnouncementService extends BaseService
{
    protected AnnouncementRepository $repo;

    public function __construct(AnnouncementRepository $repo)
    {
        parent::__construct();
        $this->repo = $repo;
    }

    public function list(array $filters = [], int $perPage = 15)
    {
        return $this->repo->paginate($filters, $perPage);
    }

    public function get(int $id)
    {
        $item = $this->repo->find($id);
        if (!$item) return null;
        if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
            if ($item->company_id !== auth('api')->user()->company_id) {
                return null;
            }
        }
        return $item;
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            return $this->repo->create($data);
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $item = $this->repo->find($id);
            if (!$item) return null;
            if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
                if ($item->company_id !== auth('api')->user()->company_id) {
                    return null;
                }
            }
            $ok = $this->repo->update($id, $data);
            if ($ok) {
                return $this->repo->find($id);
            }
            return null;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $item = $this->repo->find($id);
            if (!$item) return false;
            if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
                if ($item->company_id !== auth('api')->user()->company_id) {
                    return false;
                }
            }
            return (bool) $this->repo->delete($id);
        });
    }
}
