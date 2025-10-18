<?php

namespace App\Services;

use App\Repositories\AnnouncementRepository;
use Illuminate\Support\Facades\DB;

class AnnouncementService
{
    protected AnnouncementRepository $repo;

    public function __construct(AnnouncementRepository $repo)
    {
        $this->repo = $repo;
    }

    public function list(array $filters = [], int $perPage = 15)
    {
        return $this->repo->paginate($filters, $perPage);
    }

    public function get(int $id)
    {
        return $this->repo->find($id);
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
            return $this->repo->update($id, $data);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->repo->delete($id);
        });
    }
}
