<?php

namespace App\Actions\Posting;

use App\Posting;
use App\PostingProduct;
use App\ProductBatch;
use App\v2\Models\ProductWriteOff;
use App\v2\Models\WriteOff;

class AcceptPostingAction {

    public function handle(Posting $posting) {
        return \DB::transaction(function () use ($posting) {
            $posting->accept();
            $posting->items->each(function (PostingProduct $item) use ($posting) {
                $batch = ProductBatch::create([
                    'product_id' => $item->product_id,
                    'purchase_price' => $item->purchase_price,
                    'store_id' => $posting->store_id,
                    'quantity' => $item->quantity,
                ]);
                $item->batch()->create([
                    'product_batch_id' => $batch->id,
                    'quantity' => $item->quantity,
                ]);
            });
        });
    }
}
