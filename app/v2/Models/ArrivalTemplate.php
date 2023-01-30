<?php

namespace App\v2\Models;

use Illuminate\Database\Eloquent\Model;

class ArrivalTemplate extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'products' => 'array'
    ];
}
