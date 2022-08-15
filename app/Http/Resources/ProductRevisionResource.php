<?php

namespace App\Http\Resources;

use App\v2\Models\ProductSku;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductSku
 */
class ProductRevisionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $quantity = $this->getQuantity($request->get('store_id', 1));

        return [
            'id' => $this->id,
            'product_name' => $this->product_name,
            'categories' => $this->category->category_name,
            'attributes' => collect($this->attributes)->map(function ($attribute) {
                return [
                    'attribute_value' => $attribute->attribute_value,
                    'attribute_name' => $attribute->attribute_name->attribute_name,
                ];
            })->merge(collect($this->product->attributes)->map(function ($attribute) {
                return [
                    'attribute_value' => $attribute->attribute_value,
                    'attribute_name' => $attribute->attribute_name->attribute_name,
                ];
            }))->pluck('attribute_value')->join('|'),
            'manufacturer' => $this->manufacturer->manufacturer_name,
            'product_price' => $this->product_price,
            'quantity' => intval($quantity)
        ];
    }
}
