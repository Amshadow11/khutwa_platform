<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResumeSection extends Model
{
    protected $fillable = [
        'resume_id',
        'section_key',
        'title',
        'source_type',
        'is_visible',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
        'settings' => 'array',
    ];

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ResumeSectionItem::class)->orderBy('sort_order');
    }
}
