<?php

namespace App\Http\Controllers\VerifierPlatform;

use App\Http\Controllers\Controller;
use App\Services\VerifierPlatform\VerifierVerifyService;
use App\Formatters\ApiOutput;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class VerifierVerifyController extends Controller
{
    protected $verifyService;
    protected $apiOutput;

    public function __construct(VerifierVerifyService $verifyService, ApiOutput $apiOutput)
    {
        $this->verifyService = $verifyService;
        $this->apiOutput = $apiOutput;
    }

    /**
     * 預檢查 barcode（驗證可用性並返回車號列表）
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function preCheck(Request $request): JsonResponse
    {
        try {
            // 驗證請求參數
            $validator = Validator::make($request->all(), [
                'barcode' => 'required|string|max:255',
            ], [
                'barcode.required' => 'Barcode 為必填欄位',
                'barcode.string' => 'Barcode 必須為字串',
                'barcode.max' => 'Barcode 長度不能超過255個字元',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    $this->apiOutput->failFormat('驗證失敗', $validator->errors(), 422),
                    422
                );
            }

            // 取得目前登入的核銷人員
            $verifier = auth('verifier')->user();
            if (!$verifier) {
                return response()->json(
                    $this->apiOutput->failFormat('未登入或登入已過期', [], 401),
                    401
                );
            }

            // 執行預檢查
            $result = $this->verifyService->preCheck(
                $request->input('barcode'),
                $verifier->company_id
            );

            if ($result['success']) {
                return response()->json(
                    $this->apiOutput->successFormat($result['data'], $result['message']),
                    200
                );
            } else {
                return response()->json(
                    $this->apiOutput->failFormat($result['message'], [], 200),
                    200
                );
            }
        } catch (\Exception $e) {
            Log::error('預檢查 API 錯誤', [
                'barcode' => $request->input('barcode'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(
                $this->apiOutput->exceptionFormat('系統錯誤，請稍後再試', 500, $e),
                500
            );
        }
    }

    /**
     * 單筆核銷
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verify(Request $request): JsonResponse
    {
        try {
            // 驗證請求參數
            $validator = Validator::make($request->all(), [
                'barcode' => 'required|string|max:255',
                'vehicle_id' => 'nullable|integer|exists:cleaner_vehicles,id',
                'driver_name' => 'nullable|string|max:255',
            ], [
                'barcode.required' => 'Barcode 為必填欄位',
                'barcode.string' => 'Barcode 必須為字串',
                'barcode.max' => 'Barcode 長度不能超過255個字元',
                'vehicle_id.integer' => '車輛 ID 必須為整數',
                'vehicle_id.exists' => '車輛不存在',
                'driver_name.string' => '司機名字必須為字串',
                'driver_name.max' => '司機名字長度不能超過255個字元',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    $this->apiOutput->failFormat('驗證失敗', $validator->errors(), 422),
                    422
                );
            }

            // 取得目前登入的核銷人員
            $verifier = auth('verifier')->user();
            if (!$verifier) {
                return response()->json(
                    $this->apiOutput->failFormat('未登入或登入已過期', [], 401),
                    401
                );
            }

            // 執行核銷（包含 company_id 權限檢查）
            $result = $this->verifyService->verify(
                $request->input('barcode'),
                $verifier->id,
                $verifier->company_id,
                $request->input('vehicle_id'),
                $request->input('driver_name')
            );

            if ($result['success']) {
                return response()->json(
                    $this->apiOutput->successFormat($result['data'], $result['message']),
                    200
                );
            } else {
                return response()->json(
                    $this->apiOutput->failFormat($result['message'], [], 200),
                    200
                );
            }
        } catch (\Exception $e) {
            Log::error('核銷 API 錯誤', [
                'barcode' => $request->input('barcode'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(
                $this->apiOutput->exceptionFormat('系統錯誤，請稍後再試', 500, $e),
                500
            );
        }
    }

    /**
     * 批量核銷
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function batchVerify(Request $request): JsonResponse
    {
        try {
            // 驗證請求參數
            $validator = Validator::make($request->all(), [
                'barcodes' => 'required|array',
                'barcodes.*' => 'required|string|max:255',
            ], [
                'barcodes.required' => 'Barcode 列表為必填欄位',
                'barcodes.array' => 'Barcode 列表必須為陣列',
                'barcodes.*.required' => '每個 Barcode 為必填',
                'barcodes.*.string' => '每個 Barcode 必須為字串',
                'barcodes.*.max' => '每個 Barcode 長度不能超過255個字元',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    $this->apiOutput->failFormat('驗證失敗', $validator->errors(), 422),
                    422
                );
            }

            // 取得目前登入的核銷人員
            $verifier = auth('verifier')->user();
            if (!$verifier) {
                return response()->json(
                    $this->apiOutput->failFormat('未登入或登入已過期', [], 401),
                    401
                );
            }

            // 執行批量核銷（包含 company_id 權限檢查）
            $result = $this->verifyService->batchVerify(
                $request->input('barcodes'),
                $verifier->id,
                $verifier->company_id
            );

            if ($result['success']) {
                return response()->json(
                    $this->apiOutput->successFormat($result['data'], $result['message']),
                    200
                );
            } else {
                return response()->json(
                    $this->apiOutput->failFormat($result['message'], $result['data'] ?? [], 200),
                    200
                );
            }
        } catch (\Exception $e) {
            Log::error('批量核銷 API 錯誤', [
                'barcodes_count' => count($request->input('barcodes', [])),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(
                $this->apiOutput->exceptionFormat('系統錯誤，請稍後再試', 500, $e),
                500
            );
        }
    }

    /**
     * 取得核銷統計
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            // 取得目前登入的核銷人員
            $verifier = auth('verifier')->user();
            if (!$verifier) {
                return response()->json(
                    $this->apiOutput->failFormat('未登入或登入已過期', [], 401),
                    401
                );
            }

            // 取得統計
            $result = $this->verifyService->getVerifyStats($verifier->id);

            if ($result['success']) {
                return response()->json(
                    $this->apiOutput->successFormat($result['data'], '取得統計成功'),
                    200
                );
            } else {
                return response()->json(
                    $this->apiOutput->failFormat($result['message'], $result['data'] ?? [], 200),
                    200
                );
            }
        } catch (\Exception $e) {
            Log::error('取得核銷統計 API 錯誤', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(
                $this->apiOutput->exceptionFormat('系統錯誤，請稍後再試', 500, $e),
                500
            );
        }
    }
}
