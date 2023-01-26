<?php

namespace App\Actions\Accounting;

use App\Sale;
use App\v2\Models\Checkout;
use App\v2\Models\WithDrawal;
use App\v2\Models\WorkingDay;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CollectAccountingShiftsReportAction {

    private $start;
    private $finish;
    private $store_id;
    private $checkouts;
    private array $ranges;

    public function handle($date, $store_id) {
        $this->start = Carbon::parse($date)->startOfMonth();
        $this->finish = Carbon::parse($date)->endOfMonth();
        $this->store_id = $store_id;
        $this->ranges = get_dates_range($this->start, $this->finish);
        return $this->collectWorkingDays();
    }

    private function collectWorkingDays (): Collection {
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

        $this->checkouts = Checkout::query()
            ->whereDate('created_at', '>=', $this->start)
            ->whereDate('created_at', '<=', $this->finish)
            ->get();

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
            'cash_sales' => $this->getCashSales($item, $key, $allDays),
            'kaspi_sales' => $this->getKaspiSales($item),
            'cashless_sales' => $this->getCashlessSales($item),
            //'jysan_sales' => $this->getJysanSales($item),
            'total_sales' => $this->getTotalSales($item),
            'with_drawals' => $this->getWithDrawals($item),
            'closing_day_cash_in_hand' => $this->getClosingCashInHand($item),
            'other_checkins' => $this->getCheckins($item)
        ];
    }

    private function getCheckins($item): array {
        if ($item['empty']) {
            return [
                'cash' => 0,
                'cashless' => 0
            ];
        }
        $needleCheckouts =  $this->checkouts->filter(function ($checkout) use ($item) {
            return Carbon::parse($item['days'][0]['created_at'])
                ->isSameDay(Carbon::parse($checkout['created_at'])) && $checkout->store_id === $item['days'][0]['store_id'];
        });

        return [
            'cash' => $needleCheckouts->where('account', 0)->reduce(function ($a, $c) {
                return $a + $c['amount'];
            }, 0),
            'cashless' => $needleCheckouts->where('account', 1)->reduce(function ($a, $c) {
                return $a + $c['amount'];
            }, 0),
        ];
    }

    private function getWithDrawals($item): array {
        $types = WithDrawal::WITHDRAWAL_TYPES;
        if ($item['empty']) {
            return [
                'total' => 0,
                'incassation' => 0,
                'cash' => [
                    'by_types' => [],
                    'total_without_inc' => 0,
                ],
                'cashless' => [
                    'by_types' => [],
                    'total_without_inc' => 0,
                ],
            ];
        }
        return [
            'total' => collect($item['days'])->reduce(function ($a, $c) {
                return $a + collect($c['withdrawal'])->reduce(function ($_a, $_c) {
                    return $_a + $_c['amount'];
                    }, 0);
            }, 0),
            'incassation' => collect($item['days'])->reduce(function ($a, $c) {
                return $a + collect($c['withdrawal'])->where('type_id', 6)->reduce(function ($_a, $_c) {
                        return $_a + $_c['amount'];
                    }, 0);
            }, 0),
            'cash' => [
                'by_types' => collect($types)->map(function ($type, $index) use ($item) {
                    return [
                        'name' => $type,
                        'amount' => collect($item['days'])->reduce(function ($a, $c) use ($index) {
                            return $a + collect($c['withdrawal'])->where('account', 0)->where('type_id', $index)->reduce(function ($_a, $_c) {
                                    return $_a + $_c['amount'];
                                }, 0);
                        }),
                        'id' => $index
                    ];
                }),
                'total_without_inc' =>  collect($item['days'])->reduce(function ($a, $c) {
                    return $a + collect($c['withdrawal'])->where('account', 0)->where('type_id', '!=', 6)->reduce(function ($_a, $_c) {
                            return $_a + $_c['amount'];
                        }, 0);
                }, 0),
            ],
            'cashless' => [
                'by_types' => collect($types)->map(function ($type, $index) use ($item) {
                    return [
                        'name' => $type,
                        'amount' => collect($item['days'])->reduce(function ($a, $c) use ($index) {
                            return $a + collect($c['withdrawal'])->where('account', 1)->where('type_id', $index)->reduce(function ($_a, $_c) {
                                    return $_a + $_c['amount'];
                                }, 0);
                        }),
                        'id' => $index
                    ];
                }),
                'total_without_inc' =>  collect($item['days'])->reduce(function ($a, $c) {
                    return $a + collect($c['withdrawal'])->where('account', 1)->where('type_id', '!=', 6)->reduce(function ($_a, $_c) {
                            return $_a + $_c['amount'];
                        }, 0);
                }, 0),
            ],
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

            $byReport = optional($prevDay)->closing_cash_in_hand ?? 0;
        } else {
            $prevElement = $items[$index - 1];
            if ($prevElement['empty']) {
                $byReport = 0;
            } else {
                $prevDay = end($prevElement['days']);
                $byReport = $prevDay['closing_cash_in_hand'];
            }
        }
        $byFact = $items[$index]['days'][0]['opening_cash_in_hand'] ?? 0;

        return [
            'report' => $byReport,
            'fact' => $byFact,
            'diff' => $byFact - $byReport
        ];
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

    private function getCashSales($item, $key, $allDays): array {
        if ($item['empty']) {
            return $this->emptySaleReport();
        }

        $byShift = $this->getClosingCashInHand($item)
            - $this->getPrevDayCashInHand($allDays, $key)['fact']
            - $this->getCheckins($item)['cash']
            + $this->getWithDrawals($item)['incassation']
            + $this->getWithDrawals($item)['cash']['total_without_inc']
        ;
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
            return $a + $c['kaspi_transfers_cash'];
        }, 0);
        $byCRM = $this->getTotalSaleAmount($item, __hardcoded([3]));
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

    private function getCashlessSales($item): array {
        if ($item['empty']) {
            return $this->emptySaleReport();
        }

        $byShift = collect($item['days'])->reduce(function ($a, $c) {
            return $a + $c['jysan_transfers_cash'] + $c['kaspi_terminal_cash'] + $c['cashless_payment'];
        }, 0);
        $byCRM = $this->getTotalSaleAmount($item, __hardcoded([1, 8, 2]));
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
