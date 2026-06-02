<?php

namespace App\Actions\ATS;

use App\Models\Application;
use App\Models\ApplicationInterview;
use App\Models\Company;

class ScheduleInterviewAction
{
    public function __construct(private readonly LogApplicationActivityAction $activity)
    {
    }

    public function execute(Application $application, Company $company, array $data): ApplicationInterview
    {
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

        $application->updateStatus(Application::STATUS_INTERVIEW, 'تمت جدولة مقابلة.', silent: false);

        $this->activity->execute($application, $company, 'interview_scheduled', 'تمت جدولة مقابلة مع المرشح.', [
            'scheduled_at' => $interview->scheduled_at?->toISOString(),
        ]);

        return $interview;
    }
}
