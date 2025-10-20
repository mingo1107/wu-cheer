<?php
namespace App\Http\Controllers;

use App\Services\CommonService;
use App\Formatters\ApiOutput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommonController extends Controller
{
    protected CommonService $service;
    protected ApiOutput $apiOutput;

    public function __construct(CommonService $service, ApiOutput $apiOutput)
    {
        $this->service   = $service;
        $this->apiOutput = $apiOutput;
    }

    public function getCleanerList(): JsonResponse
    {
        try {
            $list = $this->service->getCleanerList();
            return response()->json($this->apiOutput->successFormat($list, '清運業者列表取得成功'));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得清運業者列表失敗：' . $e->getMessage(), [], 500));
        }
    }

    public function getCustomerList(): JsonResponse
    {
        try {
            $list = $this->service->getCustomerList();
            return response()->json($this->apiOutput->successFormat($list, '客戶列表取得成功'));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得客戶列表失敗：' . $e->getMessage(), [], 500));
        }
    }

    /**
     * 取得土單工程 datalist（可搜尋、狀態過濾）
     * status: all|active|inactive
     * q: 關鍵字（批號/工程/客戶）
     */
    public function getEarthDataDatalist(Request $request): JsonResponse
    {
        try {
            $status = $request->get('status', 'all');
            $q      = trim((string)$request->get('q', ''));

            $query = DB::table('earth_data as e')
                ->leftJoin('customers as c', 'c.id', '=', 'e.customer_id')
                ->select('e.id', 'e.batch_no', 'e.project_name', 'e.status', DB::raw('c.customer_name as customer_name'));

            if (Auth::guard('api')->check() && isset(Auth::guard('api')->user()->company_id)) {
                $query->where('e.company_id', Auth::guard('api')->user()->company_id);
            }

            if (in_array($status, ['active', 'inactive'], true)) {
                $query->where('e.status', $status);
            }

            if ($q !== '') {
                $query->where(function ($sub) use ($q) {
                    $sub->where('e.batch_no', 'like', "%{$q}%")
                        ->orWhere('e.project_name', 'like', "%{$q}%")
                        ->orWhere('c.customer_name', 'like', "%{$q}%");
                });
            }

            $rows = $query->orderByDesc('e.created_at')->limit(300)->get();

            $data = $rows->map(function ($r) {
                $text = trim(($r->batch_no ?: '') . ' - ' . ($r->project_name ?: ''));
                if (!empty($r->customer_name)) {
                    $text .= ' (' . $r->customer_name . ')';
                }
                return [
                    'id' => $r->id,
                    'text' => $text,
                    'batch_no' => $r->batch_no,
                    'project_name' => $r->project_name,
                    'customer_name' => $r->customer_name,
                    'status' => $r->status,
                ];
            });

            return response()->json($this->apiOutput->successFormat($data, '工程清單取得成功'));
        } catch (\Exception $e) {
            return response()->json($this->apiOutput->failFormat('取得工程清單失敗：' . $e->getMessage(), [], 500));
        }
    }
}
