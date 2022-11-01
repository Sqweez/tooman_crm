<?php

namespace App\Http\Controllers\api\v2;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkingDay\CloseWorkingDayRequest;
use App\Http\Requests\WorkingDay\CreateWorkingDayRequest;
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
            WorkingDay::create($payload);
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

        if (!$day) {
            return response()->json([
                'message' => 'При закрытии смены произошла непредвиденная ошибка! Свяжитесь с управляющим.'
            ], 403);
        }

        $day->update($payload);
        return response()->noContent();
    }
}
