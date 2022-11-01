<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\UserRole
 *
 * @property int $id
 * @property string $role_name
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole whereRoleName($value)
 * @mixin \Eloquent
 */
class UserRole extends Model
{
    protected  $guarded = [];

    const ROLE_FRANCHISE = 'franchise';
    const ROLE_ADMIN = 'admin';
    const ADMIN_ROLE_ID = 1;
    const BOSS_ROLE_ID = 8;
    const SELLER_ROLE_ID = 2;
    const SENIOR_SELLER_ROLE_ID = 9;
}
