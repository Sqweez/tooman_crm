<?php

namespace App\v2\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductWriteOff extends Model
{

    protected $guarded = ['id'];

    public function writeOff(): BelongsTo {
        return $this->belongsTo(WriteOff::class);
    }

    public function sku(): BelongsTo {
        return $this->belongsTo(Sku::class, 'product_id');
    }
}
