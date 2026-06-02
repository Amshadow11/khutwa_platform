<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserExperience extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'company_name',
        'company_id',
        'employment_type',
        'location',
        'is_remote',
        'start_date',
        'end_date',
        'is_current',
        'summary',
        'highlights',
        'skills_snapshot',
        'source',
        'confidence_score',
        'sort_order',
    ];

    protected $casts = [
        'is_remote' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'highlights' => 'array',
        'skills_snapshot' => 'array',
        'confidence_score' => 'decimal:3',
        'sort_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
