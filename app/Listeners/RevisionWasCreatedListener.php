<?php

namespace App\Listeners;

use App\Events\RevisionWasCreated;
use App\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RevisionWasCreatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param RevisionWasCreated $event
     * @return void
     */
    public function handle(RevisionWasCreated $event)
    {
        $revision = $event->revision;
        $user = $revision->user;
        if (!$user->is_super_user) {
            $user->update([
                'token' => \Str::random(60)
            ]);
        }
    }
}
