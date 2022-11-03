<?php

namespace App;

use App\v2\Models\WorkingDay;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

/**
 * App\User
 *
 * @property int $id
 * @property int $role_id
 * @property int $store_id
 * @property string|null $token
 * @property string $name
 * @property string $login
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\UserRole $role
 * @property-read \App\Store $store
 * @property-read boolean $is_super_user
 * @method static \Illuminate\Database\Eloquent\Builder|User login($login)
 * @method static \Illuminate\Database\Eloquent\Builder|User sellers()
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Query\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User token($token)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|User withoutTrashed()
 * @mixin \Eloquent
 * @property-read bool $is_non_revision_pages_blocked
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Revision[] $revisions
 * @property-read int|null $revisions_count
 */
class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    protected $guarded = [];

    protected $hidden = [
        'token'
    ];

    const IRON_WEB_STORE = 2;

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    public function store(): BelongsTo {
        return $this->belongsTo('App\Store', 'store_id');
    }

    public function role(): BelongsTo {
        return $this->belongsTo('App\UserRole', 'role_id');
    }

    public function revisions(): HasMany {
        return $this->hasMany(Revision::class, 'user_id');
    }

    public function activeRevision() {
        return $this->hasOne(Revision::class)
            ->where('status', Revision::STATUS_STARTED)
            ->first();
    }

    public function activeWorkingDay(): HasOne {
        return $this->hasOne(WorkingDay::class)
            ->whereNull('closed_at');
    }

    public function scopeLogin($q, $login) {
        $q->where('login', $login);
    }

    public function scopeToken($q, $token) {
        $q->where('token', $token);
    }

    public function scopeSellers($q) {
        return $q->whereIn('role_id', [2, 9]);
    }

    public function getIsSuperUserAttribute(): bool {
        return in_array($this->role_id, [UserRole::ADMIN_ROLE_ID, UserRole::BOSS_ROLE_ID]);
    }

    public function getIsNonRevisionPagesBlockedAttribute(): bool {
        return !$this->getIsSuperUserAttribute() && !!$this->activeRevision();
    }

    public function getIsSellerAttribute(): bool {
        return in_array($this->role_id, [UserRole::SELLER_ROLE_ID, UserRole::SENIOR_SELLER_ROLE_ID]);
    }

    public function hasOpenedWorkingDay(): bool {
        return isset($this->activeWorkingDay);
    }

    public function anotherSellerAtWork(): bool {
        return $this->store->activeWorkingDay && $this->store->activeWorkingDay->user_id !== auth()->id();
    }
}
