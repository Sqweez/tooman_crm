<?php

namespace App\v2\Models;

use App\Revision;
use App\Store;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WriteOff extends Model
{
    protected $guarded = ['id'];

    const STATUS_PENDING = 1;
    const STATUS_ACCEPTED = 2;
    const STATUS_DECLINED = 3;

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasMany(ProductWriteOff::class, 'write_off_id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo {
        return $this->belongsTo(Store::class);
    }

    public function revision(): BelongsTo {
        return $this->belongsTo(Revision::class);
    }

    public function acceptor(): BelongsTo {
        return $this->belongsTo(User::class, 'accepted_by_id');
    }
}
