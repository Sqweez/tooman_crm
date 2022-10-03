<?php

namespace App\Actions\WriteOff;

use App\ProductBatch;
use App\v2\Models\ProductWriteOff;
use App\v2\Models\WriteOff;

class AcceptWriteOffAction {

    public function handle(WriteOff $writeOff) {
        return \DB::transaction(function () use ($writeOff) {
            $writeOff->accept();
            $writeOff->items->each(function (ProductWriteOff $item) use ($writeOff) {
                $needleQnt = $item->quantity;
                while ($needleQnt > 0) {
                    $batch = ProductBatch::query()
                        ->where('quantity', '>', 0)
                        ->where('store_id', $writeOff->store_id)
                        ->where('product_id', $item->product_id)
                        ->first();

                    if ($batch) {
                        $availableQnt = min($needleQnt, $batch->quantity);
                        $batch->decrement('quantity', $availableQnt);
                        $item->batch()->create([
                            'product_batch_id' => $batch->id,
                            'quantity' => $availableQnt,
                        ]);
                        $needleQnt -= $availableQnt;
                    } else {
                        $needleQnt = 0;
                    }
                }
            });
        });
    }
}
