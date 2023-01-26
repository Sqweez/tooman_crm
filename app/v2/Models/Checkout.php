<?php

namespace App\v2\Models;

use App\Store;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\v2\Models\Checkout
 *
 * @property int $id
 * @property int $amount
 * @property int $user_id
 * @property int $store_id
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Store $store
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Checkout newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Checkout newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Checkout query()
 * @method static \Illuminate\Database\Eloquent\Builder|Checkout whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checkout whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checkout whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checkout whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checkout whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checkout whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checkout whereUserId($value)
 * @mixin \Eloquent
 * @property int $account
 * @method static \Illuminate\Database\Eloquent\Builder|Checkout whereAccount($value)
 */
class Checkout extends Model
{
    protected $guarded = [];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class)->select(['id', 'name']);
    }

    public function store(): BelongsTo {
        return $this->belongsTo(Store::class)->select(['id', 'name']);
    }
}
