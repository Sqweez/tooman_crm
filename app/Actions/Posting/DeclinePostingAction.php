<?php

namespace App\Actions\Posting;

use App\Posting;
class DeclinePostingAction {

    public function handle(Posting $posting) {
        $posting->decline();
    }
}
