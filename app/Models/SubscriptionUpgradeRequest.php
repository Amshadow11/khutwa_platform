<?php

namespace App\Models;

use App\Enums\UpgradeRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionUpgradeRequest extends Model
{
    protected $fillable = [
        'company_id',
        'from_plan_id',
        'to_plan_id',
        'months',
        'amount',
        'status',
        'notes',
        'approved_by',
        'rejected_by',
        'admin_notes',
        'rejection_reason',
        'approved_at',
        'rejected_at',
        'cancelled_at',
        'expires_at',
        'payment_intent_id',
        'payment_method',
        'payment_reference',
        'resulting_subscription_id',
        'stripe_session_id',
    ];

    protected $casts = [
        // ← Laravel cast تلقائي — status يُحوَّل لـ Enum عند القراءة
        'status'       => UpgradeRequestStatus::class,
        'approved_at'  => 'datetime',
        'rejected_at'  => 'datetime',
        'cancelled_at' => 'datetime',
        'expires_at'   => 'datetime',
        'amount'       => 'decimal:2',
        'months'       => 'integer',
    ];

    // ========================================================
    // العلاقات
    // ========================================================

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * الخطة التي كانت عليها الشركة وقت الطلب.
     * nullable — الشركة كانت على free بدون اشتراك
     */
    public function fromPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'from_plan_id');
    }

    /**
     * الخطة المطلوبة.
     */
    public function toPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'to_plan_id');
    }

    /**
     * الأدمن الذي وافق.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * الأدمن الذي رفض.
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * الاشتراك الذي نشأ بعد الموافقة.
     */
    public function resultingSubscription(): BelongsTo
    {
        return $this->belongsTo(CompanySubscription::class, 'resulting_subscription_id');
    }

    // ========================================================
    // Scopes — جميع الحالات مغطاة بدون اعتماد على string literals
    // ========================================================

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', UpgradeRequestStatus::Pending->value);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', UpgradeRequestStatus::Approved->value);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', UpgradeRequestStatus::Rejected->value);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', UpgradeRequestStatus::Cancelled->value);
    }

    /**
     * الطلبات القابلة للمعالجة (pending فقط).
     */
    public function scopeActionable(Builder $query): Builder
    {
        return $query->pending();
    }

    /**
     * الطلبات النشطة لشركة معينة (pending فقط).
     */
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * الطلبات المنتهية الصلاحية.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', UpgradeRequestStatus::Pending->value)
                     ->whereNotNull('expires_at')
                     ->where('expires_at', '<', now());
    }

    // ========================================================
    // State Checks — يعتمد على Enum وليس strings
    // ========================================================

    public function isPending(): bool
    {
        return $this->status === UpgradeRequestStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === UpgradeRequestStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->status === UpgradeRequestStatus::Rejected;
    }

    public function isCancelled(): bool
    {
        return $this->status === UpgradeRequestStatus::Cancelled;
    }

    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }

    public function isExpired(): bool
    {
        return $this->isPending()
            && $this->expires_at
            && $this->expires_at->isPast();
    }

    /**
     * هل يمكن للشركة إلغاؤه？
     */
    public function canBeCancelledByCompany(): bool
    {
        return $this->status->canBeCancelledByCompany();
    }

    // ========================================================
    // Accessors
    // ========================================================

    /**
     * اسم الحالة بالعربية — للعرض في Views.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    /**
     * لون الـ badge — للعرض في Views.
     */
    public function getStatusColorAttribute(): string
    {
        return $this->status->color();
    }

    /**
     * وصف الترقية — "من Basic إلى Pro".
     */
    public function getUpgradeDescriptionAttribute(): string
    {
        $from = $this->fromPlan?->name ?? 'مجاني';
        $to   = $this->toPlan?->name ?? 'غير محدد';
        return "من {$from} إلى {$to}";
    }

    // ========================================================
    // Static Helpers
    // ========================================================

    /**
     * هل للشركة طلب pending حالياً؟
     */
    public static function hasPendingRequest(int $companyId): bool
    {
        return static::where('company_id', $companyId)
            ->pending()
            ->exists();
    }

    /**
     * الطلب الـ pending الحالي للشركة.
     */
    public static function getPendingRequest(int $companyId): ?static
    {
        return static::where('company_id', $companyId)
            ->pending()
            ->with(['toPlan', 'fromPlan'])
            ->latest()
            ->first();
    }
}