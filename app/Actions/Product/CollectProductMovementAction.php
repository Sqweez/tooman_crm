<?php

namespace App\Actions\Product;

use App\Arrival;
use App\Posting;
use App\Sale;
use App\Transfer;
use App\v2\Models\WriteOff;

class CollectProductMovementAction {

    public function handle($product_id, $store_id) {
        $output = collect([]);
        $sales = Sale::query()
            ->where('store_id', $store_id)
            ->whereHas('products', function ($query) use ($product_id) {
                $query->where('product_id', $product_id);
            })
            ->withCount(['products' => function ($query) use ($product_id) {
                $query->where('product_id', $product_id);
            }])
            ->orderBy('created_at')
            ->get()
            ->map(function ($sale) {
                return [
                    'created_at' => $sale->created_at,
                    'date' => format_datetime($sale->created_at),
                    'name' => sprintf(
                        'Продажа №%s от %s',
                        $sale->id,
                        format_datetime($sale->created_at)
                    ),
                    'count' => $sale->products_count * -1
                ];
            });

        $outTransfers = Transfer::query()
            ->with('child_store:id,name')
            ->where('parent_store_id', $store_id)
            ->whereHas('batches', function ($query) use ($product_id) {
                $query->where('product_id', $product_id);
            })
            ->withCount(['batches' => function ($query) use ($product_id) {
                $query->where('product_id', $product_id);
            }])
            ->orderBy('created_at')
            ->get()
            ->map(function ($transfer) {
                return [
                    'created_at' => $transfer->created_at,
                    'date' => format_datetime($transfer->created_at),
                    'name' => sprintf(
                        'Перемещение №%s от %s на склад %s',
                        $transfer->id,
                        format_datetime($transfer->created_at),
                        $transfer->child_store->name,
                    ),
                    'count' => $transfer->batches_count * -1
                ];
            });


        $inTransfers = Transfer::query()
            ->with('parent_store:id,name')
            ->where('child_store_id', $store_id)
            ->where('is_accepted', true)
            ->whereHas('batches', function ($query) use ($product_id) {
                $query->where('product_id', $product_id)->where('is_transferred', true);
            })
            ->withCount(['batches' => function ($query) use ($product_id) {
                $query->where('product_id', $product_id)->where('is_transferred', true);
            }])
            ->orderBy('created_at')
            ->get()
            ->map(function ($transfer) {
                return [
                    'created_at' => $transfer->created_at,
                    'date' => format_datetime($transfer->created_at),
                    'name' => sprintf(
                        'Перемещение №%s от %s со склада %s',
                        $transfer->id,
                        format_datetime($transfer->created_at),
                        $transfer->parent_store->name,
                    ),
                    'count' => $transfer->batches_count
                ];
            });

        $arrivals = Arrival::query()
            ->where('store_id', $store_id)
            ->where('is_completed', true)
            ->whereHas('products', function ($query) use ($product_id) {
                $query->where('product_id', $product_id);
            })
            ->with(['products' => function ($query) use ($product_id) {
                $query->where('product_id', $product_id);
            }])
            ->orderBy('created_at')
            ->get()
            ->map(function ($arrival) {
                return [
                    'created_at' => $arrival->created_at,
                    'date' => format_datetime($arrival->created_at),
                    'name' => sprintf(
                        'Приемка №%s от %s',
                        $arrival->id,
                        format_datetime($arrival->created_at),
                    ),
                    'count' => $arrival->products->reduce(function ($a, $c) {
                        return $a + $c->count;
                    }, 0)
                ];
            });

        $writeOffs = WriteOff::query()
            ->where('store_id', $store_id)
            ->where('status', WriteOff::STATUS_ACCEPTED)
            ->whereHas('items', function ($query) use ($product_id) {
                $query->where('product_id', $product_id);
            })
            ->with(['items' => function ($query) use ($product_id) {
                $query->where('product_id', $product_id);
            }])
            ->orderBy('created_at')
            ->get()
            ->map(function ($writeOff) {
                return [
                    'created_at' => $writeOff->created_at,
                    'date' => format_datetime($writeOff->created_at),
                    'name' => sprintf(
                        'Списание №%s от %s',
                        $writeOff->id,
                        format_datetime($writeOff->created_at),
                    ),
                    'count' => $writeOff->items->reduce(function ($a, $c) {
                            return $a + $c->quantity;
                        }, 0) * -1
                ];
            })
            ->values();

        $postings = Posting::query()
            ->where('store_id', $store_id)
            ->where('status', Posting::STATUS_ACCEPTED)
            ->whereHas('items', function ($query) use ($product_id) {
                $query->where('product_id', $product_id);
            })
            ->with(['items' => function ($query) use ($product_id) {
                $query->where('product_id', $product_id);
            }])
            ->orderBy('created_at')
            ->get()
            ->map(function ($writeOff) {
                return [
                    'created_at' => $writeOff->created_at,
                    'date' => format_datetime($writeOff->created_at),
                    'name' => sprintf(
                        'Оприходование №%s от %s',
                        $writeOff->id,
                        format_datetime($writeOff->created_at),
                    ),
                    'count' => $writeOff->items->reduce(function ($a, $c) {
                        return $a + $c->quantity;
                    }, 0)
                ];
            })
            ->values();

        $mappedOutput = [];

        $output = $sales
            ->mergeRecursive($outTransfers)
            ->mergeRecursive($inTransfers)
            ->mergeRecursive($arrivals)
            ->mergeRecursive($writeOffs)
            ->mergeRecursive($postings)
            ->sortBy('created_at')
            ->values()
            ->toArray();

        foreach ($output as $index => $item) {
            $startQuantity = 0;
            $finalQuantity = $item['count'];
            if ($index > 0) {
                $startQuantity = $mappedOutput[$index - 1]['final_quantity'];
                $finalQuantity = $startQuantity + $item['count'];
            }
            $mappedOutput[] =
                array_merge_recursive($item, [
                    'start_quantity' => $startQuantity,
                    'final_quantity' => $finalQuantity
                ]);
        }

        return $mappedOutput;
    }
}
