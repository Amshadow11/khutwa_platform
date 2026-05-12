<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanFeature extends Model
{
    protected $fillable = [
        'plan_id',
        'feature_key',
        'feature_value',
    ];

    // ========================================================
    // العلاقات
    // ========================================================

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    // ========================================================
    // Typed Accessors
    // ========================================================

    public function asInt(): int
    {
        return (int) $this->feature_value;
    }

    public function asBool(): bool
    {
        return in_array($this->feature_value, ['true', '1', 'yes', '-1']);
    }

    public function asecimal(): float
    {
        return (float) $this->feature_value;
    }

    public function isUnlimited(): bool
    {
        return $this->feature_value === '-1';
    }

    public function isEnabled(): bool
    {
        return in_array($this->feature_value, ['true', '1', 'yes']);
    }
}