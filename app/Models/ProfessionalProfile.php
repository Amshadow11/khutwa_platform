<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessionalProfile extends Model
{
    protected $fillable = [
        'user_id',
        'headline',
        'current_title',
        'current_company',
        'industry',
        'seniority_level',
        'location_country',
        'location_city',
        'open_to_work',
        'profile_visibility',
        'public_sections',
        'preferred_job_types',
        'preferred_locations',
        'profile_completeness_score',
        'profile_completed_at',
        'last_indexed_at',
        'search_document',
        'ai_profile_summary',
    ];

    protected $casts = [
        'open_to_work' => 'boolean',
        'public_sections' => 'array',
        'preferred_job_types' => 'array',
        'preferred_locations' => 'array',
        'profile_completeness_score' => 'decimal:2',
        'profile_completed_at' => 'datetime',
        'last_indexed_at' => 'datetime',
        'search_document' => 'array',
        'ai_profile_summary' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOpenToWork(Builder $query): Builder
    {
        return $query->where('open_to_work', true);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('profile_visibility', 'public');
    }

    public function isSectionPublic(string $section): bool
    {
        $sections = $this->public_sections;

        if (! is_array($sections)) {
            return true;
        }

        return in_array($section, $sections, true);
    }
}
