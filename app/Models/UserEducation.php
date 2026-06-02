<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserEducation extends Model
{
    use SoftDeletes;

    protected $table = 'user_educations';

    protected $fillable = [
        'user_id',
        'institution_name',
        'degree',
        'field_of_study',
        'grade',
        'start_date',
        'end_date',
        'is_current',
        'description',
        'activities',
        'source',
        'confidence_score',
        'sort_order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'activities' => 'array',
        'confidence_score' => 'decimal:3',
        'sort_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
