<?php

namespace App\Http\Resources\Posting;

use App\PostingProduct;
use Illuminate\Http\Resources\Json\JsonResource;

/* @mixin PostingProduct */

class PostingProductsResource extends JsonResource
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
            'purchase_price' => $this->purchase_price,
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
