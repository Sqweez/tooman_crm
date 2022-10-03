<?php

namespace App\Http\Resources\Posting;

use App\Http\Resources\v2\Revision\RevisionsListResource;
use App\Http\Resources\WriteOff\WriteOffProductListResource;
use App\Posting;
use Illuminate\Http\Resources\Json\JsonResource;

/* @mixin Posting */

class SinglePostingResource extends JsonResource
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
            'document_no' => str_pad($this->id, 11, '0', STR_PAD_LEFT),
            'user' => $this->user,
            'acceptor' => $this->acceptor,
            'revision' => RevisionsListResource::make($this->revision),
            'store' => $this->store,
            'description' => $this->description ?: '-',
            'status' => $this->status,
            'status_text' => $this->status_text,
            'created_at' => format_date($this->created_at),
            'accepted_at' => format_date($this->accepted_at),
            'declined_at' => format_date($this->declined_at),
            'products' => PostingProductsResource::collection($this->items),
        ];
    }
}
