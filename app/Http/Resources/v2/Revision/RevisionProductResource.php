<?php

namespace App\Http\Resources\v2\Revision;

use App\RevisionProducts;
use App\v2\Models\ProductSku;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RevisionProducts
 * */

class RevisionProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_id' => $this->sku->id,
            'product_name' => $this->sku->product_name,
            'manufacturer' => $this->sku->product->manufacturer->manufacturer_name,
            'category' => $this->sku->category->category_name,
            'category_id' => $this->sku->category->id,
            'stock_quantity' => $this->stock_quantity,
            'fact_quantity' => $this->fact_quantity,
            'delta' => $this->delta,
            'product_price' => $this->price,
            'purchase_price' => $this->purchase_price,
            'stock_product_price_sum' => $this->stock_price_sum,
            'fact_product_price_sum' => $this->fact_price_sum,
            'product_price_sum_delta' => $this->price_sum_delta,
            'attributes' => collect($this->sku->attributes)->map(function ($attribute) {
                return [
                    'attribute_value' => $attribute->attribute_value,
                    'attribute_name' => $attribute->attribute_name->attribute_name,
                ];
            })->merge(collect($this->sku->product->attributes)->map(function ($attribute) {
                return [
                    'attribute_value' => $attribute->attribute_value,
                    'attribute_name' => $attribute->attribute_name->attribute_name,
                ];
            }))->pluck('attribute_value')->join('|'),
            'full_product_name' => $this->sku->full_product_name,
        ];
    }
}
