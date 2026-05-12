<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class CompanySubscription extends Model
{
    protected $fillable = [
        'company_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'cancelled_at',
        'payment_method',
        'payment_reference',
        'amount_paid',
        'notes',
    ];

    protected $casts = [
        'starts_at'     => 'datetime',
        'ends_at'       => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at'  => 'datetime',
        'amount_paid'   => 'decimal:2',
    ];

    // ========================================================
    // العلاقات
    // ========================================================

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    // ========================================================
    // Scopes
    // ========================================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
                     ->where(fn($q) => $q
                         ->whereNull('ends_at')
                         ->orWhere('ends_at', '>', now())
                     );
    }

    public function scopeTrial(Builder $query): Builder
    {
        return $query->where('status', 'trial')
                     ->where('trial_ends_at', '>', now());
    }

    // ========================================================
    // Methods
    // ========================================================

    public function isActive(): bool
    {
        if ($this->status === 'trial') {
            return $this->trial_ends_at && $this->trial_ends_at->isFuture();
        }
        if ($this->status !== 'active') return false;
        return is_null($this->ends_at) || $this->ends_at->isFuture();
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function daysRemaining(): int
    {
        $end = $this->status === 'trial' ? $this->trial_ends_at : $this->ends_at;
        if (! $end) return 9999;
        return max(0, (int) now()->diffInDays($end, false));
    }
}