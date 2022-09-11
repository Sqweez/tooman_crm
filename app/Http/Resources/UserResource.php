<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @mixin User
 * */

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'store_id' => $this->is_super_user ? -1 : $this->store_id,
            'city' => $this->is_super_user ? 'Все города' : $this->store->city,
            'login' => $this->login,
            'role_id' => $this->role_id,
            'role' => $this->role->role_name,
            'store_slug' => Str::slug($this->store->city_name->name),
            'is_super_user' => $this->is_super_user,
            'store' => $this->is_super_user ? ['id' => -1, 'name' => 'Все склады'] : $this->store,
        ];
    }
}
