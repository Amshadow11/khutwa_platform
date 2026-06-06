<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class JobApplicationMatch extends Model
{
    use HasFactory, Searchable;

    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'job_match_run_id',
        'application_id',
        'job_id',
        'company_id',
        'resume_snapshot_hash',
        'resume_snapshot_version',
        'job_snapshot_hash',
        'matching_version',
        'match_cache_key',
        'overall_score',
        'skills_score',
        'experience_score',
        'education_score',
        'location_score',
        'seniority_score',
        'matched_skills',
        'missing_skills',
        'evidence',
        'risk_flags',
        'ai_explanation',
        'status',
        'is_reused',
        'reused_from_match_id',
        'evaluated_at',
        'error_message',
    ];

    protected $casts = [
        'resume_snapshot_version' => 'integer',
        'matching_version' => 'integer',
        'overall_score' => 'decimal:2',
        'skills_score' => 'decimal:2',
        'experience_score' => 'decimal:2',
        'education_score' => 'decimal:2',
        'location_score' => 'decimal:2',
        'seniority_score' => 'decimal:2',
        'matched_skills' => 'array',
        'missing_skills' => 'array',
        'evidence' => 'array',
        'risk_flags' => 'array',
        'is_reused' => 'boolean',
        'evaluated_at' => 'datetime',
    ];

    public function searchableAs(): string
    {
        return config('scout.prefix') . 'job_application_matches';
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing('application:id,applicant_name,job_id,user_id,resume_snapshot', 'application.user:id,full_name,username');

        return [
            'id' => (int) $this->id,
            'company_id' => (int) $this->company_id,
            'job_id' => (int) $this->job_id,
            'application_id' => (int) $this->application_id,
            'job_match_run_id' => (int) $this->job_match_run_id,
            'overall_score' => $this->overall_score !== null ? (float) $this->overall_score : null,
            'skills_score' => $this->skills_score !== null ? (float) $this->skills_score : null,
            'experience_score' => $this->experience_score !== null ? (float) $this->experience_score : null,
            'education_score' => $this->education_score !== null ? (float) $this->education_score : null,
            'location_score' => $this->location_score !== null ? (float) $this->location_score : null,
            'seniority_score' => $this->seniority_score !== null ? (float) $this->seniority_score : null,
            'matched_skills' => $this->matched_skills ?? [],
            'missing_skills' => $this->missing_skills ?? [],
            'risk_flags' => $this->risk_flags ?? [],
            'candidate_name' => $this->application?->candidate_name,
            'candidate_headline' => $this->application?->candidate_headline,
            'candidate_location' => $this->application?->candidate_location,
            'resume_snapshot_hash' => $this->resume_snapshot_hash,
            'resume_snapshot_version' => (int) $this->resume_snapshot_version,
            'job_snapshot_hash' => $this->job_snapshot_hash,
            'matching_version' => (int) $this->matching_version,
            'status' => $this->status,
            'is_reused' => (bool) $this->is_reused,
            'evaluated_at' => $this->evaluated_at?->timestamp,
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(JobMatchRun::class, 'job_match_run_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function reusedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reused_from_match_id');
    }
}
