<?php

namespace App;

use App\v2\Models\ProductWriteOff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Posting
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
 * @property-read \App\User|null $acceptor
 * @property-read string $status_text
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PostingProduct[] $items
 * @property-read int|null $items_count
 * @property-read \App\Revision|null $revision
 * @property-read \App\Store $store
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Posting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Posting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Posting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Posting whereAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Posting whereAcceptedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Posting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Posting whereDeclinedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Posting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Posting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Posting whereRevisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Posting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Posting whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Posting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Posting whereUserId($value)
 * @mixin \Eloquent
 */
class Posting extends Model
{
    protected $guarded = ['id'];

    const STATUS_PENDING = 1;
    const STATUS_ACCEPTED = 2;
    const STATUS_DECLINED = 3;

    const WITH_PRODUCTS = [
        'items.sku.product:id,product_name,manufacturer_id,grouping_attribute_id,category_id',
        'items.sku.product.manufacturer', 'items.sku.product.attributes', 'items.sku.product.category', 'items.sku.product.prices',
        'items.sku.product.attributes.attribute_name', 'items.sku.attributes', 'items.sku.attributes.attribute_name',
        //'store:id,name', 'user:id,name'
    ];

    public function items(): HasMany {
        return $this->hasMany(PostingProduct::class, 'posting_id');
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

    public function accept(): bool {
        return $this->update([
            'accepted_at' => now(),
            'status' => self::STATUS_ACCEPTED,
            'accepted_by_id' => auth()->id()
        ]);
    }

    public function decline() {
        $this->update([
            'status' => self::STATUS_DECLINED,
            'declined_at' => now(),
        ]);
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
