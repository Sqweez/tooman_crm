<?php

namespace App\v2\Models;

use App\Revision;
use App\Store;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\v2\Models\WriteOff
 *
 * @property int $id
 * @property int $user_id
 * @property int $store_id
 * @property int|null $revision_id
 * @property string|null $description
 * @property int $status
 * @property int|null $accepted_by_id
 * @property string|null $accepted_at
 * @property string|null $declined_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User|null $acceptor
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\v2\Models\ProductWriteOff[] $items
 * @property-read int|null $items_count
 * @property-read Revision|null $revision
 * @property-read Store $store
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|WriteOff newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WriteOff newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WriteOff query()
 * @method static \Illuminate\Database\Eloquent\Builder|WriteOff whereAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WriteOff whereAcceptedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WriteOff whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WriteOff whereDeclinedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WriteOff whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WriteOff whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WriteOff whereRevisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WriteOff whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WriteOff whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WriteOff whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WriteOff whereUserId($value)
 * @mixin \Eloquent
 */
class WriteOff extends Model
{
    protected $guarded = ['id'];

    const STATUS_PENDING = 1;
    const STATUS_ACCEPTED = 2;
    const STATUS_DECLINED = 3;

    const WITH_PRODUCTS = [
        'items.sku.product:id,product_name,manufacturer_id,grouping_attribute_id,category_id',
        'items.sku.product.manufacturer', 'items.sku.product.attributes', 'items.sku.product.category', 'items.sku.product.prices',
        'items.sku.product.attributes.attribute_name', 'items.sku.attributes', 'items.sku.attributes.attribute_name',
    ];

    public function items(): HasMany {
        return $this->hasMany(ProductWriteOff::class, 'write_off_id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class)->select(['id', 'name']);
    }

    public function store(): BelongsTo {
        return $this->belongsTo(Store::class)->select(['id', 'name']);
    }

    public function revision(): BelongsTo {
        return $this->belongsTo(Revision::class);
    }

    public function acceptor(): BelongsTo {
        return $this->belongsTo(User::class, 'accepted_by_id')->select(['id', 'name']);
    }

    public function getStatusTextAttribute(): string {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return __('status.pending');
            case self::STATUS_ACCEPTED:
                return __('status.accepted');
            case self::STATUS_DECLINED:
                return __('status.declined');
            default:
                return 'Неизвестно';
        }
    }
}
