<?php

namespace App\Http\Resources\v2\Revision;

use App\Revision;
use Illuminate\Http\Resources\Json\JsonResource;

/**
* @mixin Revision
 */

class RevisionResource extends JsonResource
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
            'store' => $this->store,
            'user' => $this->user,
            'start_date' => $this->start_date,
            'finish_date' => $this->finish_date,
            'is_finished' => $this->is_finished,
            'products' => RevisionProductResource::collection($this->revision_products)
        ];
    }
}
