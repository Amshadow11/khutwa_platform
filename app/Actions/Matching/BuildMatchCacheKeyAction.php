<?php

namespace App\Actions\Matching;

use App\Models\Application;

class BuildMatchCacheKeyAction
{
    public function execute(
        Application $application,
        string $jobSnapshotHash,
        int $matchingVersion
    ): string {
        return hash('sha256', implode('|', [
            $application->id,
            $application->job_id,
            $application->resume_snapshot_hash ?: 'missing-snapshot-hash',
            $application->resume_snapshot_version ?: 1,
            $jobSnapshotHash,
            $matchingVersion,
        ]));
    }
}
