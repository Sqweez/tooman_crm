<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Revision
 *
 * @property int $id
 * @property int $user_id
 * @property int $store_id
 * @property int $is_finished
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\RevisionProducts|null $revision_products
 * @property-read \App\Store $store
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Revision newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Revision newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Revision query()
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereIsFinished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereUserId($value)
 * @mixin \Eloquent
 * @property string|null $finished_at
 * @property-read string|null $finish_date
 * @property-read string $start_date
 * @property-read int|null $revision_products_count
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereFinishedAt($value)
 */
class Revision extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_finished' => 'boolean',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo {
        return $this->belongsTo(Store::class);
    }

    public function revision_products(): HasMany {
        return $this->hasMany(RevisionProducts::class, 'revision_id');
    }

    public function getStartDateAttribute(): string {
        return Carbon::parse($this->created_at)->format('d.m.Y H:i:s');
    }

    public function getFinishDateAttribute(): ?string {
        return $this->finished_at ? Carbon::parse($this->finished_at)->format('d.m.Y H:i:s') : null;
    }
}
