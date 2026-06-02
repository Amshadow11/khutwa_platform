<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResumeTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'view_path',
        'supports_rtl',
        'is_active',
        'settings_schema',
        'preview_image_path',
    ];

    protected $casts = [
        'supports_rtl' => 'boolean',
        'is_active' => 'boolean',
        'settings_schema' => 'array',
    ];

    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class, 'template_id');
    }
}
