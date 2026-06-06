<?php

namespace App\Actions\ATS;

use App\Models\Application;
use App\Models\ApplicationInterview;
use App\Models\Company;
use App\Services\ATS\ApplicationStatusWorkflowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduleInterviewAction
{
    public function __construct(
        private readonly LogApplicationActivityAction $activity,
        private readonly TransitionApplicationStatusAction $transition,
        private readonly ApplicationStatusWorkflowService $workflow,
    ) {}

    public function execute(Application $application, Company $company, array $data): ApplicationInterview
    {
        $interview = DB::transaction(function () use ($application, $company, $data) {
            if (
                $application->status !== Application::STATUS_INTERVIEW
                && ! $this->workflow->canTransition($application, Application::STATUS_INTERVIEW)
            ) {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن جدولة مقابلة لهذا الطلب من حالته الحالية.',
                ]);
            }

            $interview = $application->interviews()->create([
                'company_id' => $company->id,
                'scheduled_at' => $data['scheduled_at'],
                'duration_minutes' => $data['duration_minutes'] ?? 30,
                'location_type' => $data['location_type'] ?? 'online',
                'location' => $data['location'] ?? null,
                'meeting_url' => $data['meeting_url'] ?? null,
                'status' => 'scheduled',
                'notes' => $data['notes'] ?? null,
            ]);

            if ($application->status !== Application::STATUS_INTERVIEW) {
                $this->transition->execute(
                    application: $application,
                    toStatus: Application::STATUS_INTERVIEW,
                    actor: $company,
                    note: 'تمت جدولة مقابلة.',
                    transitionKey: 'schedule_interview',
                    metadata: ['interview_id' => $interview->id],
                );
            }

            return $interview;
        });

        $this->activity->execute($application, $company, 'interview_scheduled', 'تمت جدولة مقابلة مع المرشح.', [
            'scheduled_at' => $interview->scheduled_at?->toISOString(),
            'interview_id' => $interview->id,
        ]);

        return $interview;
    }
}
