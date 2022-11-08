<?php

namespace App\Actions\Accounting;

use App\Sale;
use App\v2\Models\WorkingDay;
use Carbon\Carbon;

class CollectAccountingShiftsReportAction {

    private $start;
    private $finish;
    private $store_id;
    private array $ranges;

    public function handle($date, $store_id) {
        $this->start = Carbon::parse($date)->startOfMonth();
        $this->finish = Carbon::parse($date)->endOfMonth();
        $this->store_id = $store_id;
        $this->ranges = get_dates_range($this->start, $this->finish);
        return $this->collectWorkingDays();
    }

    private function collectWorkingDays () {
        $workingDays = WorkingDay::query()
            ->whereDate('created_at', '>=', $this->start)
            ->whereDate('created_at', '<=', $this->finish)
            ->where('store_id', $this->store_id)
            ->with('sales', 'sales.products', 'withdrawal')
            ->orderBy('created_at')
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->created_at)->format('Y-m-d');
            });

        $workingDays = collect($this->ranges)
            ->map(function ($range) use ($workingDays) {
                return isset($workingDays[$range])
                    ? ['days' => $workingDays[$range]->toArray()] + ['date' => $range, 'empty' => false]
                    : ['empty' => true, 'date' => $range];
            })
            ->values();


        return $workingDays
            ->map(function ($item, $key) use ($workingDays) {
                return [
                    'date' => $item['date'],
                    'report' => $this->collectReportByDay($item, $key, $workingDays)
                ];
            });
    }

    private function collectReportByDay($item, $key, $allDays): array {
        return [
            'prev_day_cash_in_hand' => $this->getPrevDayCashInHand($allDays, $key),
            //'total_sales' => $this->getTotalSaleAmount($item),
            'cash_sales' => $this->getCashSales($item),
            'kaspi_sales' => $this->getKaspiSales($item),
            'jysan_sales' => $this->getJysanSales($item),
            'total_sales' => $this->getTotalSales($item),
            'with_drawals' => $this->getWithDrawals($item),
            'closing_day_cash_in_hand' => $this->getClosingCashInHand($item),
        ];
    }

    private function getWithDrawals($item) {
        if ($item['empty']) {
            return [
                'total' => 0
            ];
        }
        return [
            'total' => collect($item['days'])->reduce(function ($a, $c) {
                return $a + collect($c['withdrawal'])->reduce(function ($_a, $_c) {
                    return $_a + $_c['amount'];
                    }, 0);
            }, 0)
        ];
    }

    private function getClosingCashInHand($item): int {
        if ($item['empty']) {
            return 0;
        }

        $lastShift = end($item['days']);
        return intval($lastShift['closing_cash_in_hand']);
    }

    private function getPrevDayCashInHand($items, $index) {
        if ($index === 0) {
            $prevDay = WorkingDay::query()
                ->whereDate('created_at', '<', $this->start)
                ->latest()
                ->first();

            return optional($prevDay)->closing_cash_in_hand ?? 0;
        }
        $prevElement = $items[$index - 1];
        if ($prevElement['empty']) {
            return 0;
        }
        $prevDay = end($prevElement['days']);
        return $prevDay['closing_cash_in_hand'];
    }

    private function getTotalSaleAmount($item, $paymentType = null) {
        if ($item['empty']) {
            return 0;
        }
        return collect($item['days'])->reduce(function ($a, $c) use ($paymentType) {
            $sales = $paymentType === null
                ? collect($c['sales'])
                : collect($c['sales'])->filter(function ($sale) use ($paymentType) {
                    return (is_array($paymentType)
                        ? in_array($sale['payment_type'], $paymentType)
                        : $paymentType === $sale['payment_type']) || $sale['payment_type'] === __hardcoded(5);
                })
                    ->values();
            return $a + $sales->reduce(function ($_a, $_c) use ($paymentType) {
                if ($_c['payment_type'] !== __hardcoded(5) || is_null($paymentType)) {
                    $price = $_c['final_price'];
                } else {
                    $splitPayment = collect($_c['split_payment']);
                    $price = $splitPayment->filter(function ($s) use ($paymentType) {
                        return is_array($paymentType) ?
                            in_array($s['payment_type'], $paymentType)
                            : $s['payment_type'] === $paymentType;
                    })->reduce(function ($a, $c) {
                        return $a + $c['amount'];
                    }, 0);
                }
                return $_a + $price;
                }, 0);
        }, 0);
    }

    private function getCashSales($item): array {
        if ($item['empty']) {
            return $this->emptySaleReport();
        }

        $byShift = collect($item['days'])->reduce(function ($a, $c) {
            return $a + $c['hard_cash'];
        }, 0);
        $byCRM = $this->getTotalSaleAmount($item, __hardcoded(0));
        $diff = $byCRM - $byShift;

        return [
            'by_shift' => $byShift,
            'by_crm' => $byCRM,
            'diff' => $diff
        ];
    }

    private function getKaspiSales($item): array {
        if ($item['empty']) {
            return $this->emptySaleReport();
        }

        $byShift = collect($item['days'])->reduce(function ($a, $c) {
            return $a + $c['kaspi_terminal_cash'] + $c['kaspi_transfers_cash'];
        }, 0);
        $byCRM = $this->getTotalSaleAmount($item, __hardcoded([1, 3, 2]));
        $diff = $byCRM - $byShift;

        return [
            'by_shift' => $byShift,
            'by_crm' => $byCRM,
            'diff' => $diff
        ];
    }

    private function getJysanSales($item): array {
        if ($item['empty']) {
            return $this->emptySaleReport();
        }

        $byShift = collect($item['days'])->reduce(function ($a, $c) {
            return $a + $c['jysan_transfers_cash'];
        }, 0);
        $byCRM = $this->getTotalSaleAmount($item, __hardcoded(8));
        $diff = $byCRM - $byShift;

        return [
            'by_shift' => $byShift,
            'by_crm' => $byCRM,
            'diff' => $diff
        ];
    }

    private function getTotalSales($item): array {
        if ($item['empty']) {
            return $this->emptySaleReport();
        }
        $byShift = collect($item['days'])->reduce(function ($a, $c) {
            return $a + $c['total_by_shift'];
        }, 0);
        $byCRM = $this->getTotalSaleAmount($item);
        $diff = $byCRM - $byShift;

        return [
            'by_shift' => $byShift,
            'by_crm' => $byCRM,
            'diff' => $diff
        ];
    }

    /**
     * @return array
     */
    private function emptySaleReport(): array {
        return ['by_shift' => 0, 'by_crm' => 0, 'diff' => 0];
    }
}
