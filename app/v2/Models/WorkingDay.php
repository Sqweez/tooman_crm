<?php

namespace App\v2\Models;

use App\Sale;
use App\Store;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\v2\Models\WorkingDay
 *
 * @property int $id
 * @property int $user_id
 * @property int $store_id
 * @property int|null $opening_cash_in_hand
 * @property int|null $closing_cash_in_hand
 * @property int|null $kaspi_terminal_cash
 * @property int|null $kaspi_transfers_cash
 * @property int|null $jysan_transfers_cash
 * @property string|null $closed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|WorkingDay newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkingDay newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkingDay query()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkingDay whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkingDay whereClosingCashInHand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkingDay whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkingDay whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkingDay whereJysanTransfersCash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkingDay whereKaspiTerminalCash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkingDay whereKaspiTransfersCash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkingDay whereOpeningCashInHand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkingDay whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkingDay whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkingDay whereUserId($value)
 * @mixin \Eloquent
 * @property int $is_enabled
 * @property int|null $hard_cash
 * @property-read \Illuminate\Database\Eloquent\Collection|Sale[] $sales
 * @property-read int|null $sales_count
 * @property-read Store $store
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|WorkingDay whereHardCash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkingDay whereIsEnabled($value)
 * @property-read int $total_by_shift
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\v2\Models\WithDrawal[] $withdrawal
 * @property-read int|null $withdrawal_count
 */
class WorkingDay extends Model
{
    protected $guarded = ['id'];

    protected $appends = [
        'total_by_shift'
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo {
        return $this->belongsTo(Store::class);
    }

    public function sales(): HasMany {
        return $this->hasMany(Sale::class);
    }

    public function withdrawal(): HasMany {
        return $this->hasMany(WithDrawal::class);
    }

    public function getTotalByShiftAttribute(): int {
        return $this->hard_cash + $this->jysan_transfers_cash + $this->kaspi_terminal_cash + $this->kaspi_transfers_cash;
    }
}
