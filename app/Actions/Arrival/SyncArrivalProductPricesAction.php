<?php

namespace App\Actions\Arrival;

use App\Price;
use App\Product;

class SyncArrivalProductPricesAction {

    public function handle(array $products, $store_id = null) {
        collect($products)->each(function ($product) use ($store_id) {
            if (is_null($store_id)) {
                Product::query()
                    ->where('product_price', '!=', $product['product_price'])
                    ->where('id', $product['base_product_id'])
                    ->update(['product_price' => $product['product_price']]);
            } else {
                Price::query()
                    ->updateOrCreate(
                        ['product_id' => $product['base_product_id'], 'store_id' => $store_id],
                        ['price' => $product['product_price']]
                    );
            }
        });
    }

}
