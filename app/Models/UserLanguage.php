<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserLanguage extends Pivot
{
    protected $table = 'user_languages';

    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'language_id',
        'proficiency_level',
        'proficiency_score',
        'is_native',
        'sort_order',
    ];

    protected $casts = [
        'proficiency_score' => 'integer',
        'is_native' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
