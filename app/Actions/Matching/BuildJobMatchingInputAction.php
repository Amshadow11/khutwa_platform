<?php

namespace App\Actions\Matching;

use App\Models\Application;
use App\Models\Job;

class BuildJobMatchingInputAction
{
    public function execute(Job $job, Application $application): array
    {
        $snapshot = $application->resume_snapshot ?? [];

        return [
            'job' => [
                'id' => $job->id,
                'title' => $job->title,
                'description' => $job->description,
                'requirements' => $job->requirements,
                'location' => $job->location,
                'job_type' => $job->job_type,
                'experience_level' => $job->experience_level,
                'remote_work' => (bool) $job->remote_work,
                'salary' => $job->salary,
                'salary_range' => $job->salary_range,
            ],
            'candidate' => [
                'identity' => $snapshot['identity'] ?? [],
                'skills' => $snapshot['skills'] ?? [],
                'experiences' => $snapshot['experiences'] ?? [],
                'educations' => $snapshot['educations'] ?? [],
                'projects' => $snapshot['projects'] ?? [],
                'certifications' => $snapshot['certifications'] ?? [],
                'languages' => $snapshot['languages'] ?? [],
            ],
            'snapshot' => [
                'hash' => $application->resume_snapshot_hash,
                'version' => $application->resume_snapshot_version,
                'created_at' => $application->resume_snapshot_created_at?->toISOString(),
            ],
        ];
    }
}
