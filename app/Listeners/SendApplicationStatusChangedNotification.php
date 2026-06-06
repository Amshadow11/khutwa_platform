<?php

namespace App\Listeners;

use App\Events\ApplicationStatusTransitioned;
use App\Notifications\ApplicationStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendApplicationStatusChangedNotification implements ShouldQueue
{
    public function handle(ApplicationStatusTransitioned $event): void
    {
        if (! $event->notifyCandidate || ! $event->application->user) {
            return;
        }

        $event->application->user->notify(
            new ApplicationStatusChanged($event->application, $event->fromStatus ?? '')
        );
    }
}
