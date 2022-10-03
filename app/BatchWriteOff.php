<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\BatchWriteOff
 *
 * @property int $id
 * @property int $product_write_off_id
 * @property int $product_batch_id
 * @property int $quantity
 * @method static \Illuminate\Database\Eloquent\Builder|BatchWriteOff newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BatchWriteOff newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BatchWriteOff query()
 * @method static \Illuminate\Database\Eloquent\Builder|BatchWriteOff whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BatchWriteOff whereProductBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BatchWriteOff whereProductWriteOffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BatchWriteOff whereQuantity($value)
 * @mixin \Eloquent
 */
class BatchWriteOff extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'product_batch_id',
        'product_write_off_id',
        'quantity'
    ];
}
