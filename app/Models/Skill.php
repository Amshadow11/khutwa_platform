<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    protected $fillable = [
        'name',
        'normalized_name',
        'slug',
        'category',
        'type',
        'description',
        'is_active',
        'is_verified',
        'usage_count',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'usage_count' => 'integer',
        'metadata' => 'array',
    ];

    public function aliases(): HasMany
    {
        return $this->hasMany(SkillAlias::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_skills')
            ->using(UserSkill::class)
            ->withPivot([
                'proficiency_level',
                'proficiency_score',
                'years_experience',
                'is_featured',
                'endorsement_count',
                'source',
                'confidence_score',
                'sort_order',
                'evidence',
            ])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
