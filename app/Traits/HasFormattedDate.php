<?php

namespace App\Traits;

use Carbon\Carbon;

trait HasFormattedDate {
    public function getDateFormattedAttribute(): string {
        return Carbon::parse($this->created_at)->format('d.m.Y H:i:s');
    }
}
