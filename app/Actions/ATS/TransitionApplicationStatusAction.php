<?php

namespace App\Actions\ATS;

use App\Events\ApplicationStatusTransitioned;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Services\ATS\ApplicationStatusWorkflowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransitionApplicationStatusAction
{
    public function __construct(private readonly ApplicationStatusWorkflowService $workflow)
    {
    }

    public function execute(
        Application $application,
        string $toStatus,
        ?object $actor = null,
        ?string $note = null,
        ?string $transitionKey = null,
        array $metadata = [],
        bool $notifyCandidate = true,
    ): ApplicationStatusHistory {
        $history = DB::transaction(function () use ($application, $toStatus, $actor, $note, $transitionKey, $metadata) {
            $locked = Application::query()
                ->whereKey($application->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === $toStatus) {
                throw ValidationException::withMessages([
                    'status' => 'حالة الطلب محدثة بالفعل.',
                ]);
            }

            if (! $this->workflow->canTransition($locked, $toStatus)) {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن نقل الطلب إلى هذه الحالة من حالته الحالية.',
                ]);
            }

            $fromStatus = $locked->status;
            $resolvedKey = $transitionKey ?: $this->workflow->transitionKey($fromStatus, $toStatus);

            $locked->forceFill([
                'status' => $toStatus,
                'status_updated_at' => now(),
            ])->save();

            $history = $locked->statusHistory()->create([
                'from_status' => $fromStatus,
                'status' => $toStatus,
                'note' => $note,
                'actor_type' => $actor ? $actor::class : null,
                'actor_id' => method_exists($actor, 'getKey') ? $actor->getKey() : null,
                'transition_key' => $resolvedKey,
                'metadata' => $metadata ?: null,
                'changed_at' => now(),
            ]);

            $application->setRawAttributes($locked->getAttributes(), true);
            $application->setRelation('statusHistory', $locked->statusHistory()->get());

            return $history;
        });

        ApplicationStatusTransitioned::dispatch(
            $application->fresh(['user', 'job.company']),
            $history,
            $history->from_status,
            $history->status,
            $actor,
            $history->transition_key,
            $note,
            $notifyCandidate,
        );

        return $history;
    }
}
