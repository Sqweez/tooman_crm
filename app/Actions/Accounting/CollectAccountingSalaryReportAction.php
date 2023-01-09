<?php

namespace App\Actions\Accounting;

use App\v2\Models\Shift;
use App\v2\Models\ShiftPenalty;
use Carbon\Carbon;

class CollectAccountingSalaryReportAction {

    private $start;
    private $finish;
    private array $ranges;

    public function handle($date) {
        $this->start = Carbon::parse($date)->startOfMonth();
        $this->finish = Carbon::parse($date)->endOfMonth();
        $shiftPenalties = ShiftPenalty::whereDate('created_at', '>=', $this->start)
            ->whereDate('created_at', '<=', $this->finish)
            ->get()
            ->groupBy('user_id');

        return Shift::query()
            ->with(['store', 'user'])
            ->whereDate('created_at', '>=', $this->start)
            ->whereDate('created_at', '<=', $this->finish)
            ->get()
            ->groupBy('user_id')
            ->map(function ($items, $user_id) use ($shiftPenalties) {
                $shiftSalary = collect($items)->reduce(function ($a, $c) {
                    return $a + $c->base_tax;
                }, 0);
                $salesSalary = collect($items)->reduce(function ($a, $c) {
                    return $a + $c->sale_tax;
                }, 0);
                $currentPenalties = isset($shiftPenalties[$user_id]) ? $shiftPenalties[$user_id] : [];
                $shiftPenaltiesAmount = collect($currentPenalties)->reduce(function ($a, $c) {
                    return $a + $c['amount'];
                }, 0);
                return [
                    'user' => $items[0]['user'],
                    'user_id' => $user_id,
                    'shift_count' => count($items),
                    'shift_salary' => $shiftSalary,
                    'sale_amount' => collect($items)->reduce(function ($a, $c) {
                        return $a + $c->total_sales;
                    }, 0),
                    'sale_amount_salary' => $salesSalary,
                    'shift_penalties_amount' => $shiftPenaltiesAmount,
                    'total_salary' => $shiftSalary + $salesSalary + $shiftPenaltiesAmount,
                    'store_id' => $items[0]['store_id']
                ];
            })
            ->values();
    }
}
