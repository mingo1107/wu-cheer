<?php
namespace App\Http\Controllers;

use App\Formatters\ApiOutput;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    protected $customerService;
    protected $apiOutput;

    public function __construct(CustomerService $customerService, ApiOutput $apiOutput)
    {
        $this->customerService = $customerService;
        $this->apiOutput       = $apiOutput;
    }

    /**
     * 取得客戶列表
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
            $customers = $this->customerService->getCustomers($filters, $perPage);

            return response()->json($this->apiOutput->successFormat($customers, '客戶列表取得成功'));

        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得客戶列表失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 建立新客戶
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // 驗證請求資料
            $validator = Validator::make($request->all(), [
                'customer_name'  => 'required|string|max:255',
                'contact_person' => 'required|string|max:255',
                'phone'          => 'nullable|string|max:20',
                'email'          => 'nullable|email|max:255',
                'address'        => 'nullable|string|max:500',
                'tax_id'         => 'nullable|string|size:8|regex:/^\d{8}$/',
                'status'         => 'nullable|in:active,inactive',
                'notes'          => 'nullable|string|max:1000',
            ], [
                'customer_name.required'  => '公司名稱為必填欄位',
                'customer_name.max'       => '公司名稱不能超過255個字元',
                'contact_person.required' => '聯絡人為必填欄位',
                'contact_person.max'      => '聯絡人不能超過255個字元',
                'phone.max'               => '電話號碼不能超過20個字元',
                'email.email'             => '電子郵件格式不正確',
                'email.max'               => '電子郵件不能超過255個字元',
                'address.max'             => '地址不能超過500個字元',
                'tax_id.size'             => '統一編號必須為8位數字',
                'tax_id.regex'            => '統一編號格式不正確',
                'status.in'               => '狀態值不正確',
                'notes.max'               => '備註不能超過1000個字元',
            ]);

            if ($validator->fails()) {
                return response()->json($this->apiOutput->failFormat('資料驗證失敗', $validator->errors(), 422));
            }

            // 額外的業務邏輯驗證
            $validationErrors = $this->customerService->validateCustomerData($request->all());
            if (! empty($validationErrors)) {
                return response()->json($this->apiOutput->failFormat('資料驗證失敗', $validationErrors, 422));
            }

            $customer = $this->customerService->createCustomer($request->all());

            return response()->json($this->apiOutput->successFormat($customer, '客戶建立成功'), 201);

        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('建立客戶失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 取得客戶詳細資料
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $customer = $this->customerService->getCustomer($id);

            if (! $customer) {
                return response()->json($this->apiOutput->failFormat('客戶不存在', [], 404));
            }

            return response()->json($this->apiOutput->successFormat($customer, '客戶資料取得成功'));

        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得客戶資料失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 更新客戶資料
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
                'customer_name'  => 'sometimes|required|string|max:255',
                'contact_person' => 'sometimes|required|string|max:255',
                'phone'          => 'nullable|string|max:20',
                'email'          => 'nullable|email|max:255',
                'address'        => 'nullable|string|max:500',
                'tax_id'         => 'nullable|string|size:8|regex:/^\d{8}$/',
                'status'         => 'nullable|in:active,inactive',
                'notes'          => 'nullable|string|max:1000',
            ], [
                'customer_name.required'  => '公司名稱為必填欄位',
                'customer_name.max'       => '公司名稱不能超過255個字元',
                'contact_person.required' => '聯絡人為必填欄位',
                'contact_person.max'      => '聯絡人不能超過255個字元',
                'phone.max'               => '電話號碼不能超過20個字元',
                'email.email'             => '電子郵件格式不正確',
                'email.max'               => '電子郵件不能超過255個字元',
                'address.max'             => '地址不能超過500個字元',
                'tax_id.size'             => '統一編號必須為8位數字',
                'tax_id.regex'            => '統一編號格式不正確',
                'status.in'               => '狀態值不正確',
                'notes.max'               => '備註不能超過1000個字元',
            ]);

            if ($validator->fails()) {
                return response()->json($this->apiOutput->failFormat('資料驗證失敗', $validator->errors(), 422));
            }

            // 額外的業務邏輯驗證
            $validationErrors = $this->customerService->validateCustomerData($request->all(), $id);
            if (! empty($validationErrors)) {
                return response()->json($this->apiOutput->failFormat('資料驗證失敗', $validationErrors, 422));
            }

            $customer = $this->customerService->updateCustomer($id, $request->all());

            return response()->json($this->apiOutput->successFormat($customer, '客戶更新成功'));

        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('更新客戶失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 刪除客戶
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->customerService->deleteCustomer($id);

            if (! $deleted) {
                return response()->json($this->apiOutput->failFormat('客戶不存在或刪除失敗', [], 404));
            }

            return response()->json($this->apiOutput->successFormat(null, '客戶刪除成功'));

        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('刪除客戶失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 取得活躍客戶列表
     *
     * @return JsonResponse
     */
    public function active(): JsonResponse
    {
        try {
            $customers = $this->customerService->getActiveCustomers();

            return response()->json($this->apiOutput->successFormat($customers, '活躍客戶列表取得成功'));

        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得活躍客戶列表失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 搜尋客戶
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $keyword = $request->get('keyword', '');

            if (empty($keyword)) {
                return response()->json($this->apiOutput->failFormat('搜尋關鍵字不能為空', [], 400));
            }

            $customers = $this->customerService->searchCustomers($keyword);

            return response()->json($this->apiOutput->successFormat($customers, '客戶搜尋成功'));

        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('搜尋客戶失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 取得客戶統計資料
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->customerService->getCustomerStats();

            return response()->json($this->apiOutput->successFormat($stats, '客戶統計資料取得成功'));

        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得客戶統計資料失敗：' . $e->getMessage(), [], 500));
        }
    }
}
