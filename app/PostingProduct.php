<?php

namespace App;

use App\v2\Models\ProductSku;
use App\v2\Models\WriteOff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\PostingProduct
 *
 * @property int $id
 * @property int $product_id
 * @property int $posting_id
 * @property int $quantity
 * @property int $purchase_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\PostingBatch|null $batch
 * @property-read \App\Posting $posting
 * @property-read ProductSku $sku
 * @method static \Illuminate\Database\Eloquent\Builder|PostingProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostingProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostingProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|PostingProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostingProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostingProduct wherePostingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostingProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostingProduct wherePurchasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostingProduct whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostingProduct whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PostingProduct extends Model
{
    public $guarded = ['id'];


    public function posting(): BelongsTo {
        return $this->belongsTo(Posting::class);
    }

    public function sku(): BelongsTo {
        return $this->belongsTo(ProductSku::class, 'product_id');
    }

    public function batch(): HasOne {
        return $this->hasOne(PostingBatch::class);
    }
}
