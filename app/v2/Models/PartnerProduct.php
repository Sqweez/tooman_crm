<?php

namespace App\v2\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\v2\Models\PartnerProduct
 *
 * @property int $id
 * @property int $user_id
 * @property int $product_sku_id
 * @property int $product_id
 * @property-read \App\v2\Models\Product $product
 * @property-read \App\v2\Models\ProductSku $sku
 * @method static \Illuminate\Database\Eloquent\Builder|PartnerProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PartnerProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PartnerProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|PartnerProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PartnerProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PartnerProduct whereProductSkuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PartnerProduct whereUserId($value)
 * @mixin \Eloquent
 */
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
