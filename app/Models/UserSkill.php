<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserSkill extends Pivot
{
    protected $table = 'user_skills';

    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'skill_id',
        'proficiency_level',
        'proficiency_score',
        'years_experience',
        'is_featured',
        'endorsement_count',
        'source',
        'confidence_score',
        'sort_order',
        'evidence',
    ];

    protected $casts = [
        'proficiency_score' => 'integer',
        'years_experience' => 'decimal:1',
        'is_featured' => 'boolean',
        'endorsement_count' => 'integer',
        'confidence_score' => 'decimal:3',
        'sort_order' => 'integer',
        'evidence' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
