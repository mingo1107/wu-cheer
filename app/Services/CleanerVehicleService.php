<?php

namespace App\Services;

use App\Models\CleanerVehicle;
use App\Repositories\CleanerVehicleRepository;
use App\Repositories\CleanerRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanerVehicleService
{
    protected $vehicleRepo;
    protected $cleanerRepo;

    public function __construct(
        CleanerVehicleRepository $vehicleRepo,
        CleanerRepository $cleanerRepo
    ) {
        $this->vehicleRepo = $vehicleRepo;
        $this->cleanerRepo = $cleanerRepo;
    }

    /**
     * 取得清運業者的所有車輛
     */
    public function getByCleanerId(int $cleanerId): Collection
    {
        // 驗證清運業者是否存在
        $cleaner = $this->cleanerRepo->findById($cleanerId);
        if (!$cleaner) {
            throw new \Exception('清運業者不存在');
        }

        return $this->vehicleRepo->getByCleanerId($cleanerId);
    }

    /**
     * 取得車輛
     */
    public function get(int $id): ?CleanerVehicle
    {
        return $this->vehicleRepo->findById($id);
    }

    /**
     * 建立車輛
     */
    public function create(array $data): CleanerVehicle
    {
        // 驗證清運業者是否存在
        $cleaner = $this->cleanerRepo->findById($data['cleaner_id']);
        if (!$cleaner) {
            throw new \Exception('清運業者不存在');
        }

        // 驗證車頭車號是否重複（同一清運業者內）
        if ($this->vehicleRepo->isFrontPlateExists($data['cleaner_id'], $data['front_plate'])) {
            throw new \Exception('車頭車號已存在');
        }

        return DB::transaction(function () use ($data) {
            $vehicle = $this->vehicleRepo->createVehicle($data);
            Log::info('車輛建立成功', [
                'id' => $vehicle->id,
                'cleaner_id' => $vehicle->cleaner_id,
                'front_plate' => $vehicle->front_plate,
            ]);
            return $vehicle;
        });
    }

    /**
     * 更新車輛
     */
    public function update(int $id, array $data): ?CleanerVehicle
    {
        $vehicle = $this->vehicleRepo->findById($id);
        if (!$vehicle) {
            return null;
        }

        // 如果更新車頭車號，檢查是否重複
        if (isset($data['front_plate']) && $data['front_plate'] !== $vehicle->front_plate) {
            if ($this->vehicleRepo->isFrontPlateExists($vehicle->cleaner_id, $data['front_plate'], $id)) {
                throw new \Exception('車頭車號已存在');
            }
        }

        return DB::transaction(function () use ($id, $data) {
            $ok = $this->vehicleRepo->updateVehicle($id, $data);
            return $ok ? $this->vehicleRepo->findById($id) : null;
        });
    }

    /**
     * 刪除車輛
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $vehicle = $this->vehicleRepo->findById($id);
            if (!$vehicle) {
                return false;
            }

            $cleanerId = $vehicle->cleaner_id;
            $frontPlate = $vehicle->front_plate;

            $deleted = $this->vehicleRepo->deleteVehicle($id);

            if ($deleted) {
                Log::info('車輛刪除成功', [
                    'id' => $id,
                    'cleaner_id' => $cleanerId,
                    'front_plate' => $frontPlate,
                ]);
            }

            return $deleted;
        });
    }

    /**
     * 取得車輛數量
     */
    public function getVehicleCount(int $cleanerId): int
    {
        return $this->vehicleRepo->getVehicleCount($cleanerId);
    }
}
