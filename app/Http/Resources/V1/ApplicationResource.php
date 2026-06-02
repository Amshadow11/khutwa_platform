<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'cover_letter' => $this->cover_letter,
            'cv_url' => $this->cv_url,
            'candidate' => [
                'name' => $this->candidate_name,
                'email' => $this->candidate_email,
                'phone' => $this->candidate_phone,
                'headline' => $this->candidate_headline,
                'location' => $this->candidate_location,
                'skills_summary' => $this->snapshot_skills_summary,
            ],
            'resume_id' => $this->resume_id,
            'resume_snapshot' => $this->resume_snapshot,
            'resume_snapshot_hash' => $this->resume_snapshot_hash,
            'resume_snapshot_version' => $this->resume_snapshot_version,
            'resume_snapshot_created_at' => $this->resume_snapshot_created_at?->toISOString(),
            'submitted_resume_pdf_url' => $this->submitted_resume_pdf_url,
            'applied_at' => $this->applied_at?->toISOString(),
            'review' => $this->whenLoaded('reviews', fn () => $this->reviews->first() ? [
                'rating' => $this->reviews->first()->rating,
                'recommendation' => $this->reviews->first()->recommendation,
                'overall_score' => $this->reviews->first()->overall_score,
                'rubric_scores' => $this->reviews->first()->rubric_scores,
                'match_signals' => $this->reviews->first()->match_signals,
                'evaluated_snapshot_hash' => $this->reviews->first()->evaluated_snapshot_hash,
                'evaluated_at' => $this->reviews->first()->evaluated_at?->toISOString(),
            ] : null),
            'job' => new JobResource($this->whenLoaded('job')),
        ];
    }
}
