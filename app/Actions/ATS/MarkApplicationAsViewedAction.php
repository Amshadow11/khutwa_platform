<?php

namespace App\Actions\ATS;

use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use Illuminate\Validation\ValidationException;

class MarkApplicationAsViewedAction
{
    public function __construct(private readonly TransitionApplicationStatusAction $transition)
    {
    }

    public function execute(Application $application, ?object $actor = null): ?ApplicationStatusHistory
    {
        if ($application->status !== Application::STATUS_PENDING) {
            return null;
        }

        try {
            return $this->transition->execute(
                application: $application,
                toStatus: Application::STATUS_VIEWED,
                actor: $actor,
                transitionKey: 'mark_viewed',
                notifyCandidate: false,
            );
        } catch (ValidationException $exception) {
            return $application->fresh()->status === Application::STATUS_VIEWED ? null : throw $exception;
        }
    }
}
