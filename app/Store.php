<?php

namespace App;

use App\v2\Models\SortByNameScope;
use App\v2\Models\WorkingDay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Store
 *
 * @property int $id
 * @property string $city
 * @property int $type_id
 * @property string $name
 * @property string|null $address
 * @property string|null $description
 * @property string $kaspi_terminal_ip
 * @property int $ironBalance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $telegram_chat_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\StoreType $type
 * @method static \Illuminate\Database\Eloquent\Builder|Store newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Store newQuery()
 * @method static \Illuminate\Database\Query\Builder|Store onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Store query()
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereTelegramChatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Store withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Store withoutTrashed()
 * @mixin \Eloquent
 * @property int $city_id
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereCityId($value)
 * @property-read \App\v2\Models\City $city_name
 * @property-read mixed $balance
 * @property-read mixed $iron_balance
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CompanionTransaction[] $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Store shops()
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereKaspiTerminalIp($value)
 * @property array|null $etc
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereEtc($value)
 * @property string $iin
 * @property string $legal_name
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereIin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereLegalName($value)
 */
class Store extends Model
{

    use SoftDeletes;

    const DEFAULT_LEGAL_NAME = 'ИП "Нускабаев И.Н"';

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'type_id' => 'integer',
        'etc' => 'array',
        'meta' => 'array'
    ];

    public function type() {
        return $this->belongsTo('App\StoreType', 'type_id');
    }

    public function city_name() {
        return $this->belongsTo('App\v2\Models\City', 'city_id');
    }

    public function transactions() {
        return $this->hasMany('App\CompanionTransaction', 'companion_id');
    }

    public function getBalanceAttribute() {
        return $this->transactions()
                ->where('type', CompanionTransaction::COMPANION_OWN_BALANCE_TYPE)
                ->sum('transaction_sum') ?? 0;
    }

    public function getIronBalanceAttribute() {
        return $this->transactions()
                ->where('type', CompanionTransaction::COMPANION_IRON_BALANCE_TYPE)
                ->sum('transaction_sum') ?? 0;
    }

    public function scopeShops($q) {
        return $q->whereTypeId(1);
    }

    public function activeWorkingDay(): HasOne {
        return $this->hasOne(WorkingDay::class)
            ->whereNull('closed_at');
    }

    public function getTransformedMeta() {
        if (!$this->meta) {
          return [
              'legal_name' => Store::DEFAULT_LEGAL_NAME
          ];
        }

        return $this->meta;
    }

    protected static function boot() {
        parent::boot();
        static::addGlobalScope(new SortByNameScope('name'));
    }
}
