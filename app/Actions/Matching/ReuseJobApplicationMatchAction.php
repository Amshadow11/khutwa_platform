<?php

namespace App\Actions\Matching;

use App\Models\Application;
use App\Models\JobApplicationMatch;
use App\Models\JobMatchRun;

class ReuseJobApplicationMatchAction
{
    public function execute(JobMatchRun $run, Application $application, JobApplicationMatch $source): JobApplicationMatch
    {
        return JobApplicationMatch::query()->create([
            'job_match_run_id' => $run->id,
            'application_id' => $application->id,
            'job_id' => $application->job_id,
            'company_id' => $run->company_id,
            'resume_snapshot_hash' => $source->resume_snapshot_hash,
            'resume_snapshot_version' => $source->resume_snapshot_version,
            'job_snapshot_hash' => $source->job_snapshot_hash,
            'matching_version' => $source->matching_version,
            'match_cache_key' => $source->match_cache_key,
            'overall_score' => $source->overall_score,
            'skills_score' => $source->skills_score,
            'experience_score' => $source->experience_score,
            'education_score' => $source->education_score,
            'location_score' => $source->location_score,
            'seniority_score' => $source->seniority_score,
            'matched_skills' => $source->matched_skills,
            'missing_skills' => $source->missing_skills,
            'evidence' => $source->evidence,
            'risk_flags' => $source->risk_flags,
            'ai_explanation' => $source->ai_explanation,
            'status' => JobApplicationMatch::STATUS_COMPLETED,
            'is_reused' => true,
            'reused_from_match_id' => $source->id,
            'evaluated_at' => now(),
        ]);
    }
}
