<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Language extends Model
{
    protected $fillable = ['name', 'native_name', 'iso_code', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_languages')
            ->using(UserLanguage::class)
            ->withPivot(['proficiency_level', 'proficiency_score', 'is_native', 'sort_order'])
            ->withTimestamps();
    }
}
