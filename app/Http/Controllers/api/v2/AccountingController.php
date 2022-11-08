<?php

namespace App\Http\Controllers\api\v2;

use App\Actions\Accounting\CollectAccountingShiftsReportAction;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountingController extends Controller
{
    public function getShiftReport(Request $request, CollectAccountingShiftsReportAction $action): JsonResponse {
        $date = $request->get('date');
        $store_id = $request->get('store_id');
        $report = $action->handle($date, $store_id);
        return response()->json([
            'report' => $report
        ]);
    }
}
