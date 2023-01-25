<?php

namespace App\Http\Controllers\api\v2;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkingDay\CloseWorkingDayRequest;
use App\Http\Requests\WorkingDay\CreateWorkingDayRequest;
use App\Sale;
use App\v2\Models\Shift;
use App\v2\Models\ShiftTax;
use App\v2\Models\WorkingDay;
use Illuminate\Http\Request;

class WorkingDayController extends Controller
{
    public function store(CreateWorkingDayRequest $request) {
        $payload = $request->validated();
        $previousWorkingDay = WorkingDay::query()
            ->whereStoreId($payload['store_id'])
            ->whereNotNull('closed_at')
            ->latest()
            ->limit(1)
            ->first();

        $cashInHand = optional($previousWorkingDay)->closing_cash_in_hand ?: 0;
        $notValidCashInHand = $cashInHand !== $payload['opening_cash_in_hand'];

        if ($request->has('retry') || !$notValidCashInHand) {
            $workingDay = WorkingDay::create($payload);
            $shiftTax = ShiftTax::whereStoreId($payload['store_id'])->first();
            $baseTax = $shiftTax ? $shiftTax->shift_tax : 0;
            Shift::query()
                ->create([
                    'user_id' => $payload['user_id'],
                    'store_id' => $payload['store_id'],
                    'base_tax' => $baseTax,
                    'sale_tax' => 0,
                    'working_day_id' => $workingDay->id,
                ]);
        }
        if ($notValidCashInHand && !$request->has('retry')) {
            return response()
                ->json([
                    'message' => __('messages.not_valid_cash_in_hand')
                ], 403);
        }
        return response()->noContent();
    }


    public function close(CloseWorkingDayRequest $request) {
        $payload = $request->validated();
        $payload = \Arr::except($payload, ['working_day_id']);

        $day = WorkingDay::query()
            ->whereNull('closed_at')
            ->whereKey($request->working_day_id)
            ->first();

        $this->updateShift($day);

        if (!$day) {
            return response()->json([
                'message' => 'При закрытии смены произошла непредвиденная ошибка! Свяжитесь с управляющим.'
            ], 403);
        }

        $day->update($payload);
        return response()->noContent();
    }

    private function updateShift(WorkingDay $day) {
        $shiftRules = ShiftTax::whereStoreId($day->store_id)->first();
        $shift = Shift::where('working_day_id', $day->id)->first();
        if (!$shift) {
            $shift = Shift::query()
                ->create([
                    'user_id' => $day->user_id,
                    'store_id' => $day->store_id,
                    'base_tax' => $shiftRules->shift_tax,
                    'sale_tax' => 0,
                    'working_day_id' => $day->id,
                ])->refresh();
        }
        $shiftRules = $shiftRules->shift_rules;
        if (!$shiftRules) {
            return false;
        }
        $salesAmount = Sale::query()
            ->with('products')
            ->where('working_day_id', $day->id)
            ->where('is_opt', false)
            ->where('is_confirmed', true)
            ->get()
            ->reduce(function ($a, $c) {
                return $a + collect($c->products)->reduce(function ($_a, $_c) {
                    return $_a + $_c->product_price;
                    }, 0);
                }, 0);

        $shift->update([
            'total_sales' => $salesAmount
        ]);

        $shiftRule = collect($shiftRules)
            ->sortByDesc('threshold')
            ->filter(function ($r) use ($salesAmount) {
                return $salesAmount >= $r['threshold'];
            })
            ->first();

        if (!$shiftRule) {
            return false;
        }
        $percent =  floatval(str_replace(",", ".", $shiftRule['value']));
        $saleTax = $salesAmount * $percent / 100;
        $shift->update([
            'sale_tax' => $saleTax
        ]);

        return true;
    }
}
