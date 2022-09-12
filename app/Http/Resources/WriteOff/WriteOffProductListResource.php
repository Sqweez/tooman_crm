<?php

namespace App\Http\Resources\WriteOff;

use App\v2\Models\ProductWriteOff;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductWriteOff
 * */

class WriteOffProductListResource extends JsonResource
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
            'product_price' => $this->product_price,
            'quantity' => $this->quantity,
            'product_name' => $this->sku->product->product_name,
            'category' => $this->sku->product->category,
            'manufacturer' => $this->sku->product->manufacturer,
            'attributes' => collect($this->sku->attributes)->map(function ($attribute) {
                return [
                    'attribute_value' => $attribute->attribute_value,
                    'attribute_name' => $attribute->attribute_name->attribute_name,
                ];
            })->concat($this->sku->product->attributes->map(function ($attribute) {
                return [
                    'attribute_value' => $attribute->attribute_value,
                    'attribute_name' => $attribute->attribute_name->attribute_name,
                ];
            })),
        ];
    }
}
