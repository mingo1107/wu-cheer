<?php
namespace App\Http\Controllers;

use App\Formatters\ApiOutput;
use App\Services\VerifierService;
use App\Models\Verifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VerifierController extends Controller
{
    protected $verifierService;
    protected $apiOutput;

    public function __construct(VerifierService $verifierService, ApiOutput $apiOutput)
    {
        $this->verifierService = $verifierService;
        $this->apiOutput       = $apiOutput;
    }

    /**
     * 取得核銷人員列表
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'search'     => $request->get('search'),
                'status'     => $request->get('status'),
                'sort_by'    => $request->get('sort_by', 'created_at'),
                'sort_order' => $request->get('sort_order', 'desc'),
            ];

            $perPage   = $request->get('per_page', 15);
            $verifiers = $this->verifierService->getVerifiers($filters, $perPage);

            return response()->json($this->apiOutput->successFormat($verifiers, '核銷人員列表取得成功'));

        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得核銷人員列表失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 建立新核銷人員
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // 驗證請求資料
            $validator = Validator::make($request->all(), [
                'name'     => 'required|string|max:255',
                'account'  => 'required|string|max:255',
                'email'    => 'nullable|email|max:255',
                'phone'    => 'nullable|string|max:20',
                'password' => 'required|string|min:6|max:255',
                'status'   => 'nullable|in:active,inactive',
            ], [
                'name.required'      => '名字為必填欄位',
                'name.max'           => '名字不能超過255個字元',
                'account.required'   => '帳號為必填欄位',
                'account.max'        => '帳號不能超過255個字元',
                'email.email'        => '電子郵件格式不正確',
                'email.max'          => '電子郵件不能超過255個字元',
                'phone.max'          => '電話號碼不能超過20個字元',
                'password.required'  => '密碼為必填欄位',
                'password.min'       => '密碼長度至少需要6個字元',
                'password.max'       => '密碼不能超過255個字元',
                'status.in'          => '狀態值不正確',
            ]);

            if ($validator->fails()) {
                return response()->json($this->apiOutput->failFormat('資料驗證失敗', $validator->errors(), 422));
            }

            // 額外的業務邏輯驗證
            $validationErrors = $this->verifierService->validateVerifierData($request->all());
            if (! empty($validationErrors)) {
                return response()->json($this->apiOutput->failFormat('資料驗證失敗', $validationErrors, 422));
            }

            $verifier = $this->verifierService->createVerifier($request->all());

            return response()->json($this->apiOutput->successFormat($verifier, '核銷人員建立成功'), 201);

        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('建立核銷人員失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 取得核銷人員詳細資料
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            // 回傳欄位結構與預設值（id=0）
            if ($id === 0) {
                $schema = [];
                foreach (Verifier::FILLABLE as $field) {
                    $schema[$field] = Verifier::ATTRIBUTES[$field] ?? null;
                }
                return response()->json($this->apiOutput->successFormat($schema, '核銷人員欄位預設值'));
            }

            $verifier = $this->verifierService->getVerifier($id);

            if (! $verifier) {
                return response()->json($this->apiOutput->failFormat('核銷人員不存在', [], 404));
            }

            return response()->json($this->apiOutput->successFormat($verifier, '核銷人員資料取得成功'));

        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得核銷人員資料失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 更新核銷人員資料
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // 驗證請求資料
            $validator = Validator::make($request->all(), [
                'name'     => 'sometimes|required|string|max:255',
                'account'  => 'sometimes|required|string|max:255',
                'email'    => 'nullable|email|max:255',
                'phone'    => 'nullable|string|max:20',
                'password' => 'nullable|string|min:6|max:255',
                'status'   => 'nullable|in:active,inactive',
            ], [
                'name.required'      => '名字為必填欄位',
                'name.max'           => '名字不能超過255個字元',
                'account.required'  => '帳號為必填欄位',
                'account.max'        => '帳號不能超過255個字元',
                'email.email'        => '電子郵件格式不正確',
                'email.max'          => '電子郵件不能超過255個字元',
                'phone.max'          => '電話號碼不能超過20個字元',
                'password.min'       => '密碼長度至少需要6個字元',
                'password.max'       => '密碼不能超過255個字元',
                'status.in'         => '狀態值不正確',
            ]);

            if ($validator->fails()) {
                return response()->json($this->apiOutput->failFormat('資料驗證失敗', $validator->errors(), 422));
            }

            // 額外的業務邏輯驗證
            $validationErrors = $this->verifierService->validateVerifierData($request->all(), $id);
            if (! empty($validationErrors)) {
                return response()->json($this->apiOutput->failFormat('資料驗證失敗', $validationErrors, 422));
            }

            $verifier = $this->verifierService->updateVerifier($id, $request->all());

            return response()->json($this->apiOutput->successFormat($verifier, '核銷人員更新成功'));

        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('更新核銷人員失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 刪除核銷人員
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->verifierService->deleteVerifier($id);

            if (! $deleted) {
                return response()->json($this->apiOutput->failFormat('核銷人員不存在或刪除失敗', [], 404));
            }

            return response()->json($this->apiOutput->successFormat(null, '核銷人員刪除成功'));

        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('刪除核銷人員失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 取得活躍核銷人員列表
     *
     * @return JsonResponse
     */
    public function active(): JsonResponse
    {
        try {
            $verifiers = $this->verifierService->getActiveVerifiers();

            return response()->json($this->apiOutput->successFormat($verifiers, '活躍核銷人員列表取得成功'));

        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得活躍核銷人員列表失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 取得核銷人員統計資料
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->verifierService->getVerifierStats();

            return response()->json($this->apiOutput->successFormat($stats, '核銷人員統計資料取得成功'));

        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得核銷人員統計資料失敗：' . $e->getMessage(), [], 500));
        }
    }
}

