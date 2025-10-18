<?php
namespace App\Http\Controllers;

use App\Formatters\ApiOutput;
use App\Services\CleanerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CleanerController extends Controller
{
    protected $service;
    protected $out;

    public function __construct(CleanerService $service, ApiOutput $out)
    {
        $this->service = $service;
        $this->out = $out;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search'     => $request->get('search'),
            'status'     => $request->get('status'),
            'sort_by'    => $request->get('sort_by', 'created_at'),
            'sort_order' => $request->get('sort_order', 'desc'),
        ];
        $perPage = (int) $request->get('per_page', 15);
        $data = $this->service->list($filters, $perPage);
        return response()->json($this->out->successFormat($data, '清運業者列表取得成功'));
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cleaner_name'   => 'required|string|max:255',
            'tax_id'         => 'nullable|string|size:8|regex:/^\d{8}$/',
            'contact_person' => 'required|string|max:255',
            'phone'          => 'required|string|max:50',
            'status'         => 'nullable|in:active,inactive',
            'notes'          => 'nullable|string|max:1000',
        ], [
            'cleaner_name.required'   => '清運業者名稱為必填',
            'contact_person.required' => '聯絡人為必填',
            'phone.required'          => '聯絡電話為必填',
            'tax_id.size'             => '統一編號必須為8位數字',
            'tax_id.regex'            => '統一編號格式不正確',
        ]);

        if ($validator->fails()) {
            return response()->json($this->out->failFormat('資料驗證失敗', $validator->errors(), 422));
        }

        try {
            $item = $this->service->create($request->all());
            return response()->json($this->out->successFormat($item, '清運業者建立成功'));
        } catch (\Exception $e) {
            Log::error('清運業者建立失敗', ['error' => $e->getMessage()]);
            return response()->json($this->out->failFormat($e->getMessage(), [], 422));
        }
    }

    public function show(int $id): JsonResponse
    {
        $item = $this->service->get($id);
        if (!$item) {
            return response()->json($this->out->failFormat('清運業者不存在', [], 404));
        }
        return response()->json($this->out->successFormat($item, '清運業者取得成功'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cleaner_name'   => 'sometimes|required|string|max:255',
            'tax_id'         => 'nullable|string|size:8|regex:/^\d{8}$/',
            'contact_person' => 'sometimes|required|string|max:255',
            'phone'          => 'sometimes|required|string|max:50',
            'status'         => 'nullable|in:active,inactive',
            'notes'          => 'nullable|string|max:1000',
        ]);
        if ($validator->fails()) {
            return response()->json($this->out->failFormat('資料驗證失敗', $validator->errors(), 422));
        }

        try {
            $item = $this->service->update($id, $request->all());
            if (!$item) {
                return response()->json($this->out->failFormat('清運業者不存在或更新失敗', [], 404));
            }
            return response()->json($this->out->successFormat($item, '清運業者更新成功'));
        } catch (\Exception $e) {
            Log::error('清運業者更新失敗', ['error' => $e->getMessage()]);
            return response()->json($this->out->failFormat($e->getMessage(), [], 422));
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $ok = $this->service->delete($id);
        if (!$ok) {
            return response()->json($this->out->failFormat('清運業者不存在或刪除失敗', [], 404));
        }
        return response()->json($this->out->successFormat(['id' => $id], '清運業者刪除成功'));
    }
}
