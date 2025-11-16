<?php

namespace App\Repositories;

use App\Models\CleanerVehicle;
use Illuminate\Database\Eloquent\Collection;

class CleanerVehicleRepository extends BaseRepository
{
    public function __construct(CleanerVehicle $model)
    {
        $this->model = $model;
    }

    /**
     * 取得清運業者的所有車輛
     */
    public function getByCleanerId(int $cleanerId): Collection
    {
        return $this->model
            ->where('cleaner_id', $cleanerId)
            ->orderBy('front_plate')
            ->get();
    }

    /**
     * 根據 ID 取得車輛
     */
    public function findById(int $id): ?CleanerVehicle
    {
        return $this->model->find($id);
    }

    /**
     * 建立車輛
     */
    public function createVehicle(array $data): CleanerVehicle
    {
        return $this->model->create($data);
    }

    /**
     * 更新車輛
     */
    public function updateVehicle(int $id, array $data): bool
    {
        $vehicle = $this->findById($id);
        if (!$vehicle) {
            return false;
        }
        return $vehicle->update($data);
    }

    /**
     * 刪除車輛
     */
    public function deleteVehicle(int $id): bool
    {
        $vehicle = $this->findById($id);
        if (!$vehicle) {
            return false;
        }
        return (bool) $vehicle->delete();
    }

    /**
     * 檢查車頭車號是否已存在（同一清運業者內）
     */
    public function isFrontPlateExists(int $cleanerId, string $frontPlate, ?int $excludeId = null): bool
    {
        $query = $this->model
            ->where('cleaner_id', $cleanerId)
            ->where('front_plate', $frontPlate);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * 取得清運業者的車輛數量
     */
    public function getVehicleCount(int $cleanerId): int
    {
        return $this->model
            ->where('cleaner_id', $cleanerId)
            ->count();
    }
}
