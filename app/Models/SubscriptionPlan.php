<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_cycle',
        'trial_days',
        'sort_order',
        'is_active',
        'is_public',
        'stripe_price_id', // ← جديد
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'is_active'  => 'boolean',
        'is_public'  => 'boolean',
        'trial_days' => 'integer',
        'sort_order' => 'integer',
    ];

    // ========================================================
    // العلاقات
    // ========================================================

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class, 'plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(CompanySubscription::class, 'plan_id');
    }

    // ========================================================
    // Scopes
    // ========================================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true)->orderBy('sort_order');
    }

    /**
     * الخطط التي لها Stripe Price ID — جاهزة للدفع الإلكتروني.
     */
    public function scopeWithStripe(Builder $query): Builder
    {
        return $query->whereNotNull('stripe_price_id');
    }

    // ========================================================
    // Methods
    // ========================================================

    /**
     * جلب قيمة feature — يعتمد على features المحمّلة مسبقاً.
     */
    public function getFeature(string $key, mixed $default = null): mixed
    {
        if (! $this->relationLoaded('features')) {
            $this->load('features');
        }

        $feature = $this->features->firstWhere('feature_key', $key);
        return $feature ? $feature->feature_value : $default;
    }

    public function getFeatureInt(string $key, int $default = 0): int
    {
        return (int) $this->getFeature($key, $default);
    }

    public function getFeatureBool(string $key, bool $default = false): bool
    {
        $val = $this->getFeature($key);
        if (is_null($val)) return $default;
        return in_array($val, ['true', '1', 'yes', '-1']);
    }

    public function isFree(): bool
    {
        return $this->slug === 'free' || (float) $this->price === 0.0;
    }

    /**
     * هل الخطة مربوطة بـ Stripe؟
     * false → الدفع يدوي فقط (Manual Flow)
     * true  → يمكن الدفع الإلكتروني عبر Stripe Checkout
     */
    public function hasStripePrice(): bool
    {
        return ! empty($this->stripe_price_id);
    }
}