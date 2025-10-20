<?php
namespace App\Http\Controllers;

use App\Services\EarthPrintService;
use Illuminate\Http\Request;

class EarthPrintController extends Controller
{
    public function __construct(private readonly EarthPrintService $service) {}

    // 列印指定工程「未列印」的明細
    public function pending(Request $request, int $id)
    {
        $earth = $this->service->getEarthData($id);
        if (! $earth) {
            abort(404, '土單資料不存在');
        }

        // 權限：限定同公司（若有登入）
        if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
            if ((int)$earth->company_id !== (int)auth('api')->user()->company_id) {
                abort(403, '無權限存取');
            }
        }

        $details = $this->service->getUnprintedDetails($earth->id);
        $ids = $details->pluck('id')->all();
        if (!empty($ids)) {
            // 先標記列印時間
            $this->service->markAsPrinted($ids);
        }

        $data = [
            'earth' => $earth,
            'details' => $details,
        ];

        return view('print.earth_ticket', $data);
    }
}
