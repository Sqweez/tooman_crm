<?php

namespace App\Catalog;

use App\v2\Models\Product;
use App\v2\Models\ProductSku;

class ProductResourceResolver {

    public function resolve($product): ProductSku {
        if (!($product instanceof ProductSku)) {
            $product = ProductSku::findOrFail($product);
        }
        $product->load(ProductSku::PRODUCT_SKU_WITH_CART_LIST);
        return $product;
    }
}
