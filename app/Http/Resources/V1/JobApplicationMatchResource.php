<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobApplicationMatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_match_run_id' => $this->job_match_run_id,
            'application_id' => $this->application_id,
            'job_id' => $this->job_id,
            'company_id' => $this->company_id,
            'resume_snapshot_hash' => $this->resume_snapshot_hash,
            'resume_snapshot_version' => $this->resume_snapshot_version,
            'job_snapshot_hash' => $this->job_snapshot_hash,
            'matching_version' => $this->matching_version,
            'overall_score' => $this->overall_score,
            'skills_score' => $this->skills_score,
            'experience_score' => $this->experience_score,
            'education_score' => $this->education_score,
            'location_score' => $this->location_score,
            'seniority_score' => $this->seniority_score,
            'matched_skills' => $this->matched_skills,
            'missing_skills' => $this->missing_skills,
            'evidence' => $this->evidence,
            'risk_flags' => $this->risk_flags,
            'ai_explanation' => $this->ai_explanation,
            'status' => $this->status,
            'is_reused' => $this->is_reused,
            'reused_from_match_id' => $this->reused_from_match_id,
            'evaluated_at' => $this->evaluated_at?->toISOString(),
            'error_message' => $this->error_message,
        ];
    }
}
