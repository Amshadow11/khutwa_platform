<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationReview extends Model
{
    protected $fillable = [
        'application_id',
        'company_id',
        'rating',
        'recommendation',
        'overall_score',
        'rubric_scores',
        'match_signals',
        'evaluated_snapshot_hash',
        'evaluated_snapshot_version',
        'evaluated_at',
        'strengths',
        'concerns',
    ];

    protected $casts = [
        'rating' => 'integer',
        'overall_score' => 'decimal:2',
        'rubric_scores' => 'array',
        'match_signals' => 'array',
        'evaluated_snapshot_version' => 'integer',
        'evaluated_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
