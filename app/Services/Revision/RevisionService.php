<?php

namespace App\Services\Revision;

use App\Revision;
use App\RevisionProducts;
use App\v2\Models\ProductSku;

class RevisionService {

    public static function loadRevisionProductWithNested (Revision $revision, $withCorrect = true) {
        return RevisionProducts::query()
            ->whereRevisionId($revision->id)
            ->with(array_map(function ($item) {
                return 'sku.' . $item;
            }, ProductSku::PRODUCT_SKU_WITH_CART_LIST))
            ->when(!$withCorrect, function ($query) {
                return $query->whereRaw('fact_quantity != stock_quantity');
            })
            ->get();
    }
}
