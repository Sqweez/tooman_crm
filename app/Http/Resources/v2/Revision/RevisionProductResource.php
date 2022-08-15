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
            'stock_quantity' => $this->stock_quantity,
            'fact_quantity' => $this->fact_quantity,
            'product_price' => $this->sku->product->product_price,
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
            }))->pluck('attribute_value')->join('|')
        ];
    }
}
