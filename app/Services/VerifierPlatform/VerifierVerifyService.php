<?php

namespace App\Services\VerifierPlatform;

use App\Repositories\EarthDataDetailRepository;
use App\Repositories\CleanerVehicleRepository;
use App\Models\EarthDataDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerifierVerifyService
{
    public function __construct(
        private EarthDataDetailRepository $detailRepo,
        private CleanerVehicleRepository $vehicleRepo
    ) {}

    /**
     * 檢查 barcode 是否可用（共用方法）
     *
     * @param string $barcode
     * @param int|null $companyId 核銷人員所屬公司 ID（用於權限檢查）
     * @return array 返回格式：['success' => bool, 'message' => string, 'detail' => EarthDataDetail|null, 'earth_data' => EarthData|null]
     */
    private function checkBarcodeAvailability(string $barcode, ?int $companyId = null): array
    {
        // 驗證 barcode 格式
        if (empty($barcode)) {
            return [
                'success' => false,
                'message' => 'Barcode 不能為空',
                'detail' => null,
                'earth_data' => null
            ];
        }

        // 取得明細資料
        $detail = $this->detailRepo->findByBarcode($barcode);

        if (!$detail) {
            return [
                'success' => false,
                'message' => 'Barcode 不存在',
                'detail' => null,
                'earth_data' => null
            ];
        }

        // 權限檢查：確認此 barcode 所屬的土單是否屬於該核銷人員的公司
        $earthData = $detail->earthData;
        if (!$earthData) {
            return [
                'success' => false,
                'message' => '找不到對應的土單資料',
                'detail' => null,
                'earth_data' => null
            ];
        }

        if ($companyId !== null && (int)$earthData->company_id !== (int)$companyId) {
            return [
                'success' => false,
                'message' => '無權限核銷此 barcode（不屬於您的公司）',
                'detail' => null,
                'earth_data' => null
            ];
        }

        // 檢查是否已經核銷
        if ($detail->status === EarthDataDetail::STATUS_USED && $detail->verified_at !== null) {
            return [
                'success' => false,
                'message' => '此 barcode 已核銷，無法重複核銷',
                'detail' => null,
                'earth_data' => null
            ];
        }

        // 檢查狀態是否可核銷（必須是已列印狀態）
        $status = (int)$detail->status;

        if ($status === EarthDataDetail::STATUS_UNPRINTED) {
            return [
                'success' => false,
                'message' => '此 barcode 尚未列印，無法核銷',
                'detail' => null,
                'earth_data' => null
            ];
        }

        if ($status === EarthDataDetail::STATUS_VOIDED) {
            return [
                'success' => false,
                'message' => '此 barcode 已作廢，無法核銷',
                'detail' => null,
                'earth_data' => null
            ];
        }

        if ($status === EarthDataDetail::STATUS_RECYCLED) {
            return [
                'success' => false,
                'message' => '此 barcode 已回收，無法核銷',
                'detail' => null,
                'earth_data' => null
            ];
        }

        // 只有已列印狀態才能核銷
        if ($status !== EarthDataDetail::STATUS_PRINTED) {
            return [
                'success' => false,
                'message' => '此 barcode 狀態不允許核銷',
                'detail' => null,
                'earth_data' => null
            ];
        }

        // 所有檢查通過
        return [
            'success' => true,
            'message' => 'Barcode 可用',
            'detail' => $detail,
            'earth_data' => $earthData
        ];
    }

    /**
     * 預檢查 barcode（驗證可用性並返回車號列表）
     *
     * @param string $barcode
     * @param int|null $companyId 核銷人員所屬公司 ID（用於權限檢查）
     * @return array
     */
    public function preCheck(string $barcode, ?int $companyId = null): array
    {
        try {
            // 使用共用的 barcode 可用性檢查
            $checkResult = $this->checkBarcodeAvailability($barcode, $companyId);

            if (!$checkResult['success']) {
                return [
                    'success' => false,
                    'message' => $checkResult['message']
                ];
            }

            $detail = $checkResult['detail'];
            $earthData = $checkResult['earth_data'];

            // 取得該土單的所有清運業者
            $cleaners = $earthData->cleaners;

            if ($cleaners->isEmpty()) {
                return [
                    'success' => false,
                    'message' => '此土單沒有關聯的清運業者'
                ];
            }

            // 收集所有清運業者的車輛
            $vehicles = collect();
            foreach ($cleaners as $cleaner) {
                $cleanerVehicles = $this->vehicleRepo->getByCleanerId($cleaner->id);
                foreach ($cleanerVehicles as $vehicle) {
                    $vehicles->push([
                        'id' => $vehicle->id,
                        'front_plate' => $vehicle->front_plate,
                        'rear_plate' => $vehicle->rear_plate,
                        'cleaner_name' => $cleaner->cleaner_name,
                    ]);
                }
            }

            if ($vehicles->isEmpty()) {
                return [
                    'success' => false,
                    'message' => '此土單關聯的清運業者沒有車輛資料'
                ];
            }

            return [
                'success' => true,
                'message' => 'Barcode 可用',
                'data' => [
                    'barcode' => $barcode,
                    'earth_data' => [
                        'id' => $earthData->id,
                        'project_name' => $earthData->project_name,
                        'flow_control_no' => $earthData->flow_control_no,
                    ],
                    'vehicles' => $vehicles->toArray()
                ]
            ];
        } catch (\Exception $e) {
            Log::error('預檢查失敗', [
                'barcode' => $barcode,
                'company_id' => $companyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => '預檢查失敗：' . $e->getMessage()
            ];
        }
    }

    /**
     * 單筆核銷
     *
     * @param string $barcode
     * @param int $verifierId
     * @param int|null $companyId 核銷人員所屬公司 ID（用於權限檢查）
     * @param int|null $vehicleId 車輛 ID
     * @param string|null $driverName 司機名字
     * @return array
     */
    public function verify(string $barcode, int $verifierId, ?int $companyId = null, ?int $vehicleId = null, ?string $driverName = null): array
    {
        try {
            // 使用共用的 barcode 可用性檢查
            $checkResult = $this->checkBarcodeAvailability($barcode, $companyId);

            if (!$checkResult['success']) {
                return [
                    'success' => false,
                    'message' => $checkResult['message']
                ];
            }

            $detail = $checkResult['detail'];

            // 執行核銷（純資料庫操作，包含車號和司機名字）
            $updated = $this->detailRepo->markAsVerified($detail->id, $verifierId, $vehicleId, $driverName);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => '核銷更新失敗'
                ];
            }

            // 重新載入資料
            $detail->refresh();

            // 記錄核銷操作
            Log::info('核銷成功', [
                'barcode' => $barcode,
                'verifier_id' => $verifierId,
                'company_id' => $companyId,
                'vehicle_id' => $vehicleId,
                'driver_name' => $driverName,
                'detail_id' => $detail->id
            ]);

            return [
                'success' => true,
                'message' => '核銷成功',
                'data' => [
                    'detail' => [
                        'id' => $detail->id,
                        'barcode' => $detail->barcode,
                        'status' => $detail->status,
                        'verified_at' => $detail->verified_at,
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('核銷失敗', [
                'barcode' => $barcode,
                'verifier_id' => $verifierId,
                'company_id' => $companyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => '核銷失敗：' . $e->getMessage()
            ];
        }
    }

    /**
     * 批量核銷
     *
     * @param array $barcodes
     * @param int $verifierId
     * @param int|null $companyId 核銷人員所屬公司 ID（用於權限檢查）
     * @return array
     */
    public function batchVerify(array $barcodes, int $verifierId, ?int $companyId = null): array
    {
        try {
            if (empty($barcodes) || !is_array($barcodes)) {
                return [
                    'success' => false,
                    'message' => 'Barcode 列表不能為空'
                ];
            }

            $results = [
                'total' => count($barcodes),
                'success' => 0,
                'failed' => 0,
                'errors' => []
            ];

            // 逐筆處理核銷（每筆都進行完整的業務邏輯檢查）
            foreach ($barcodes as $barcode) {
                $result = $this->verify($barcode, $verifierId, $companyId);

                if ($result['success']) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'barcode' => $barcode,
                        'message' => $result['message'] ?? '核銷失敗'
                    ];
                }
            }

            // 記錄批量核銷操作
            Log::info('批量核銷完成', [
                'total' => $results['total'],
                'success' => $results['success'],
                'failed' => $results['failed'],
                'verifier_id' => $verifierId,
                'company_id' => $companyId
            ]);

            return [
                'success' => true,
                'message' => sprintf('批量核銷完成：成功 %d 筆，失敗 %d 筆', $results['success'], $results['failed']),
                'data' => $results
            ];
        } catch (\Exception $e) {
            Log::error('批量核銷失敗', [
                'barcodes_count' => count($barcodes),
                'verifier_id' => $verifierId,
                'company_id' => $companyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => '批量核銷失敗：' . $e->getMessage()
            ];
        }
    }

    /**
     * 取得核銷統計（今日核銷數量、總核銷數量）
     *
     * @param int $verifierId
     * @return array
     */
    public function getVerifyStats(int $verifierId): array
    {
        try {
            $today = now()->startOfDay();
            $todayEnd = now()->endOfDay();

            // 今日核銷數量
            $todayCount = EarthDataDetail::query()
                ->where('verified_by', $verifierId)
                ->whereBetween('verified_at', [$today, $todayEnd])
                ->where('status', EarthDataDetail::STATUS_USED)
                ->count();

            // 總核銷數量
            $totalCount = EarthDataDetail::query()
                ->where('verified_by', $verifierId)
                ->where('status', EarthDataDetail::STATUS_USED)
                ->whereNotNull('verified_at')
                ->count();

            return [
                'success' => true,
                'data' => [
                    'today' => $todayCount,
                    'total' => $totalCount
                ]
            ];
        } catch (\Exception $e) {
            Log::error('取得核銷統計失敗', [
                'verifier_id' => $verifierId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => '取得統計失敗',
                'data' => [
                    'today' => 0,
                    'total' => 0
                ]
            ];
        }
    }
}
