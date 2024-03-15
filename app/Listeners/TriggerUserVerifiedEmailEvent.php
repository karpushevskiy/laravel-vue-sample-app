<?php
/*
 * GorKa Team
 * Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
 */

namespace App\Listeners;

use App\Events\Broadcast\UserVerifiedEmailEvent;

/**
 * Trigger User Verified Email Event
 *
 * @package \App\Listeners
 */
class TriggerUserVerifiedEmailEvent
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
     * @param object $event
     * @return void
     */
    public function handle(object $event) : void
    {
        UserVerifiedEmailEvent::dispatch([
            'user_id' => $event->user->id,
        ]);
    }
}
