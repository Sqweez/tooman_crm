<?php

namespace App\Http\Resources\WriteOff;

use App\v2\Models\WriteOff;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WriteOff
 * */

class WriteOffListResource extends JsonResource
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
            'user' => $this->user,
            'acceptor' => $this->acceptor,
            'revision' => $this->revision,
            'description' => $this->description,
            'status' => $this->status,
            'status_text' => $this->status_text,
            'created_at' => format_date($this->created_at),
            'accepted_at' => format_date($this->accepted_at),
            'declined_at' => format_date($this->declined_at),
            'products' => WriteOffProductListResource::collection($this->items),
        ];
    }
}
