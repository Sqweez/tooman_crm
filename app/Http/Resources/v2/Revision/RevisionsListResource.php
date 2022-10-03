<?php

namespace App\Http\Resources\v2\Revision;

use App\Revision;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Revision
 * */

class RevisionsListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array {
        return [
            'id' => $this->id,
            'store' => $this->store,
            'user' => $this->user,
            'status_text' => $this->status_text,
            'date' => $this->date_formatted,
            'checking_user' => $this->checker,
            'status' => $this->status,
            'sent_to_approve_at' => format_date($this->revision_sent_to_approve_at),
            'edited_pivot_at' => format_date($this->edited_pivot_at),
            'finished_at' => format_date($this->finished_at),
            'can_sent_to_approve' => $this->can_sent_to_approve,
            'can_approve' => $this->can_approve,
            'has_not_actions' => !($this->can_sent_to_approve || $this->can_approve || $this->can_finish),
            'files' => $this->files,
            'is_pivot_generated' => !!$this->original_pivot_file,
            'can_generate_pivot_table' => $this->can_generate_pivot,
            'can_finish' => $this->can_finish,
            'can_edit' => $this->can_edit,
            'can_rollback' => $this->can_rollback,
            'is_finished' => $this->is_finished,
            'write_off_disabled' => $this->is_write_off_disabled,
            'posting_disabled' => $this->is_posting_disabled
        ];
    }
}
