<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CVParsedData extends Model
{
    protected $table = 'cv_parsed_data';

    protected $fillable = [
        'user_id',
        'application_id',
        'cv_path',
        'parsed_data',
        'confidence_score',
        'profile_updated',
        'parsed_at',
    ];

    protected $casts = [
        'parsed_data'      => 'array',
        'confidence_score' => 'decimal:2',
        'profile_updated'  => 'boolean',
        'parsed_at'        => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    // ========================================================
    // Accessors للبيانات المستخرجة
    // ========================================================

    public function getSkillsAttribute(): array
    {
        return $this->parsed_data['skills'] ?? [];
    }

    public function getExperienceAttribute(): string
    {
        $exp = $this->parsed_data['experience'] ?? [];
        if (is_array($exp)) {
            return implode("\n", array_map(fn($e) => "- {$e}", $exp));
        }
        return $exp;
    }

    public function getEducationAttribute(): string
    {
        $edu = $this->parsed_data['education'] ?? [];
        if (is_array($edu)) {
            return implode("\n", array_map(fn($e) => "- {$e}", $edu));
        }
        return $edu;
    }
}