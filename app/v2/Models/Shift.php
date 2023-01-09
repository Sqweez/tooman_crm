<?php

namespace App\v2\Models;

use App\Store;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\v2\Models\Shift
 *
 * @property int $id
 * @property int $user_id
 * @property int $store_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Store $store
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Shift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Shift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Shift query()
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereUserId($value)
 * @mixin \Eloquent
 * @property int|null $working_day_id
 * @property int $base_tax
 * @property int $sale_tax
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereBaseTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereSaleTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereWorkingDayId($value)
 * @property int $total_sales
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereTotalSales($value)
 */
class Shift extends Model
{
    protected $guarded = ['id'];

    // В выбранной смене заменяется продавец на нового
    const SHIFT_EDIT_REPLACE = 0;
    // Выбранная смена дублируется с новыми значениями
    const SHIFT_EDIT_CREATE =  1;
    // Выбранная смена удаляется
    const SHIFT_EDIT_DELETE = 2;

    protected $casts = [
        'user_id' => 'integer',
        'store_id' => 'integer'
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class)->select(['id', 'name']);
    }

    public function store(): BelongsTo {
        return $this->belongsTo(Store::class)->select(['id', 'name']);
    }
}
