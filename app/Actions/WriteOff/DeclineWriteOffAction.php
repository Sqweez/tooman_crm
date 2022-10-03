<?php

namespace App\Actions\WriteOff;

use App\v2\Models\WriteOff;

class DeclineWriteOffAction {

    public function handle(WriteOff $writeOff) {
        $writeOff->decline();
    }
}
