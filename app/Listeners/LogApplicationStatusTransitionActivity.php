<?php

namespace App\Listeners;

use App\Actions\ATS\LogApplicationActivityAction;
use App\Events\ApplicationStatusTransitioned;
use App\Models\Company;
use App\Services\ATS\ApplicationStatusWorkflowService;

class LogApplicationStatusTransitionActivity
{
    public function __construct(
        private readonly LogApplicationActivityAction $activity,
        private readonly ApplicationStatusWorkflowService $workflow,
    ) {}

    public function handle(ApplicationStatusTransitioned $event): void
    {
        $company = $event->actor instanceof Company ? $event->actor : null;

        $this->activity->execute(
            $event->application,
            $company,
            'status_transitioned',
            'تم تغيير حالة الطلب إلى ' . $this->workflow->statusLabel($event->toStatus),
            [
                'from_status' => $event->fromStatus,
                'to_status' => $event->toStatus,
                'transition_key' => $event->transitionKey,
                'history_id' => $event->history->id,
            ],
            $event->actor,
        );
    }
}
