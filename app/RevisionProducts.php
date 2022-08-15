<?php

namespace App;

use App\v2\Models\ProductSku;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\RevisionProducts
 *
 * @property int $id
 * @property int $revision_id
 * @property int $product_id
 * @property int $stock_quantity
 * @property int $fact_quantity
 * @property-read \App\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder|RevisionProducts newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RevisionProducts newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RevisionProducts query()
 * @method static \Illuminate\Database\Eloquent\Builder|RevisionProducts whereFactQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevisionProducts whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevisionProducts whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevisionProducts whereRevisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevisionProducts whereStockQuantity($value)
 * @mixin \Eloquent
 * @property-read ProductSku $sku
 */
class RevisionProducts extends Model
{
    protected $table = 'revision_products';
    protected $guarded = [];
    public $timestamps = false;

    public function sku(): BelongsTo {
        return $this->belongsTo(ProductSku::class, 'product_id');
    }
}
