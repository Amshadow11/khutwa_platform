<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeSectionItem extends Model
{
    protected $fillable = [
        'resume_section_id',
        'item_type',
        'item_id',
        'custom_payload',
        'is_visible',
        'sort_order',
    ];

    protected $casts = [
        'custom_payload' => 'array',
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(ResumeSection::class, 'resume_section_id');
    }
}
