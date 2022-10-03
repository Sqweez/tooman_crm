<?php

namespace App\v2\Models;

use App\BatchWriteOff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\v2\Models\ProductWriteOff
 *
 * @property int $id
 * @property int $product_id
 * @property int $write_off_id
 * @property int|null $batch_id
 * @property int $quantity
 * @property int $product_price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Sku $sku
 * @property-read WriteOff $writeOff
 * @method static \Illuminate\Database\Eloquent\Builder|ProductWriteOff newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductWriteOff newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductWriteOff query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductWriteOff whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductWriteOff whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductWriteOff whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductWriteOff whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductWriteOff whereProductPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductWriteOff whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductWriteOff whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductWriteOff whereWriteOffId($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|BatchWriteOff[] $batch
 * @property-read int|null $batch_count
 */
class ProductWriteOff extends Model
{

    protected $guarded = ['id'];


    public function writeOff(): BelongsTo {
        return $this->belongsTo(WriteOff::class);
    }

    public function sku(): BelongsTo {
        return $this->belongsTo(ProductSku::class, 'product_id');
    }

    public function batch(): HasMany {
        return $this->hasMany(BatchWriteOff::class, 'product_write_off_id');
    }
}
