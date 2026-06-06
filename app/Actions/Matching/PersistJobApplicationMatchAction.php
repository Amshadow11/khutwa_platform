<?php

namespace App\Actions\Matching;

use App\Models\Application;
use App\Models\JobApplicationMatch;
use App\Models\JobMatchRun;

class PersistJobApplicationMatchAction
{
    public function execute(
        JobMatchRun $run,
        Application $application,
        string $jobSnapshotHash,
        int $matchingVersion,
        string $cacheKey,
        array $score,
        ?string $aiExplanation = null
    ): JobApplicationMatch {
        return JobApplicationMatch::query()->create([
            'job_match_run_id' => $run->id,
            'application_id' => $application->id,
            'job_id' => $application->job_id,
            'company_id' => $run->company_id,
            'resume_snapshot_hash' => $application->resume_snapshot_hash,
            'resume_snapshot_version' => $application->resume_snapshot_version ?: 1,
            'job_snapshot_hash' => $jobSnapshotHash,
            'matching_version' => $matchingVersion,
            'match_cache_key' => $cacheKey,
            'overall_score' => $score['overall_score'],
            'skills_score' => $score['skills_score'],
            'experience_score' => $score['experience_score'],
            'education_score' => $score['education_score'],
            'location_score' => $score['location_score'],
            'seniority_score' => $score['seniority_score'],
            'matched_skills' => $score['matched_skills'],
            'missing_skills' => $score['missing_skills'],
            'evidence' => $score['evidence'],
            'risk_flags' => $score['risk_flags'],
            'ai_explanation' => $aiExplanation,
            'status' => JobApplicationMatch::STATUS_COMPLETED,
            'is_reused' => false,
            'evaluated_at' => now(),
        ]);
    }
}
