<?php

namespace App\Listeners;

use App\Events\UserAppliedToJob;
use App\Notifications\ApplicationReceived;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendApplicationReceivedNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(UserAppliedToJob $event): void
    {
        $application = $event->application->loadMissing('user', 'job.company');

        $application->job->company?->notify(new ApplicationReceived($application));
    }
}
