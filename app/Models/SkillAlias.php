<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillAlias extends Model
{
    protected $fillable = ['skill_id', 'alias', 'normalized_alias', 'locale'];

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
