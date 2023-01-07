<?php

namespace App\Http\Resources\Checkout;

use App\v2\Models\Checkout;
use Illuminate\Http\Resources\Json\JsonResource;
/* @mixin Checkout */
class CheckoutListResource extends JsonResource
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
            'amount' => $this->amount,
            'user' => $this->user,
            'store' => $this->store,
            'date' => format_datetime($this->created_at),
            'created_at' => $this->created_at,
            'description' => $this->description
        ];
    }
}
