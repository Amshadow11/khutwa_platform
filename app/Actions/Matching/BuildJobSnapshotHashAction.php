<?php

namespace App\Actions\Matching;

use App\Models\Job;

class BuildJobSnapshotHashAction
{
    public function execute(Job $job): string
    {
        $payload = [
            'title' => $job->title,
            'description' => $job->description,
            'requirements' => $job->requirements,
            'location' => $job->location,
            'job_type' => $job->job_type,
            'experience_level' => $job->experience_level,
            'remote_work' => (bool) $job->remote_work,
            'salary' => $job->salary,
            'salary_range' => $job->salary_range,
        ];

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
