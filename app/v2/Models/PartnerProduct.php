<?php

namespace App\v2\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerProduct extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function sku(): BelongsTo {
        return $this->belongsTo(ProductSku::class, 'product_sku_id');
    }
}
