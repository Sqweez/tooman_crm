<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PostingBatch
 *
 * @property int $id
 * @property int $posting_product_id
 * @property int $product_batch_id
 * @property int $quantity
 * @method static \Illuminate\Database\Eloquent\Builder|PostingBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostingBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostingBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder|PostingBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostingBatch wherePostingProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostingBatch whereProductBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostingBatch whereQuantity($value)
 * @mixin \Eloquent
 */
class PostingBatch extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
}
