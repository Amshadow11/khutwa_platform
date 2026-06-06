<?php

namespace App\Actions\Matching;

use App\Jobs\Matching\RunJobMatchJob;
use App\Models\Company;
use App\Models\Job;
use App\Models\JobMatchRun;

class RunJobApplicationMatchingAction
{
    public function __construct(private readonly BuildJobSnapshotHashAction $jobSnapshotHash) {}

    public function execute(Job $job, Company $company, ?object $initiatedBy = null): JobMatchRun
    {
        $provider = config('ai.features.job_matching');
        $model = config("ai.models.{$provider}");

        $run = JobMatchRun::query()->create([
            'job_id' => $job->id,
            'company_id' => $company->id,
            'initiated_by_type' => $initiatedBy ? $initiatedBy::class : null,
            'initiated_by_id' => $initiatedBy?->id,
            'status' => JobMatchRun::STATUS_QUEUED,
            'provider' => $provider,
            'model' => $model,
            'matching_version' => (int) config('ai.matching.version', 1),
            'job_snapshot_hash' => $this->jobSnapshotHash->execute($job),
            'metadata' => [
                'source' => 'company_ats',
            ],
        ]);

        RunJobMatchJob::dispatch($run->id);

        return $run;
    }
}
