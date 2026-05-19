<?php

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Authenticatable implements CanResetPasswordContract
{
    use HasFactory, Notifiable, SoftDeletes, CanResetPassword;

    // ========================================================
    // الحقول المسموح بتعبئتها (Mass Assignment Protection)
    // ========================================================
    protected $fillable = [
        'company_name',
        'email',
        'password',
        'phone',
        'phone_code',
        'logo',
        'profile_picture',
        'description',
        'address',
        'website',
        'industry',
        'founded_year',
        'company_size',
        'subscription_plan',
        'subscription',
        'subscription_started',
        'subscription_end',
        'status',
        'is_verified',
        'trial_used_at',
        'stripe_customer_id',
    ];

    // ========================================================
    // الحقول المخفية (لا تُرسل في JSON أبداً)
    // ========================================================
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ========================================================
    // تحويل الأنواع تلقائياً
    // ========================================================
    protected $casts = [
        'is_verified'          => 'boolean',
        'subscription'         => 'boolean',
        'subscription_started' => 'date',
        'subscription_end'     => 'date',
        'founded_year'         => 'integer',
        'views'                => 'integer',
        'last_login'           => 'datetime',
        'password'             => 'hashed', // تشفير تلقائي عند الحفظ
        'trial_used_at'        => 'datetime',
        'stripe_customer_id'   => 'string',
    ];

    // ========================================================
    // العلاقات (Relationships)
    // ========================================================

    /**
     * الوظائف التي نشرتها الشركة.
     * شركة واحدة → وظائف كثيرة (One-to-Many)
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    /**
     * محادثات الشركة مع الباحثين.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * إجمالي الرسائل غير المقروءة.
     */
    public function getUnreadMessagesCountAttribute(): int
    {
        return $this->conversations()->sum('company_unread');
    }

    /**
     * طلبات التوظيف المستلمة على جميع وظائف الشركة.
     * العلاقة عبر جدول وسيط (Has Many Through)
     */
    public function applications(): HasManyThrough
    {
        return $this->hasManyThrough(Application::class, Job::class);
    }
    // ========================================================
// علاقات الاشتراك
// ========================================================

/**
 * الاشتراك الحالي النشط.
 */
public function activeSubscription(): HasOne
{
    return $this->hasOne(CompanySubscription::class)
                ->where(fn($q) => $q
                    ->where('status', 'active')
                    ->orWhere('status', 'trial')
                )
                ->where(fn($q) => $q
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now())
                )
                ->latest();
}

/**
 * جميع الاشتراكات السابقة والحالية.
 */
public function subscriptions(): HasMany
{
    return $this->hasMany(CompanySubscription::class);
}

/**
 * الخطة الحالية.
 */
public function currentPlan(): ?SubscriptionPlan
{
    // Runtime cache — يُصفَّر مع كل request جديد
    if (isset($this->_currentPlan)) {
        return $this->_currentPlan;
    }

    $this->_currentPlan = $this->activeSubscription()
        ?->with('plan.features')
        ?->first()
        ?->plan
        ?? SubscriptionPlan::with('features')
                           ->where('slug', 'free')
                           ->first();

    return $this->_currentPlan;
}

// Typed property للـ cache
private ?SubscriptionPlan $_currentPlan = null;

/**
 * جلب قيمة feature من الخطة الحالية.
 *
 * مثال:
 *   $company->getPlanFeature('max_jobs_per_month') → '5'
 *   $company->getPlanFeature('featured_jobs', '0') → '2'
 */
public function getPlanFeature(string $key, mixed $default = null): mixed
{
    return $this->currentPlan()?->getFeature($key, $default) ?? $default;
}

/**
 * هل تجاوزت الشركة حد الوظائف هذا الشهر؟
 */
public function hasReachedJobLimit(): bool
{
    $limit = (int) $this->getPlanFeature('max_jobs_per_month', 2);

    if ($limit === -1) return false; // غير محدود

    $period = now()->format('Y-m');
    $used   = SubscriptionUsage::where('company_id', $this->id)
                ->where('feature_key', 'max_jobs_per_month')
                ->where('period', $period)
                ->value('used') ?? 0;

    return $used >= $limit;
}

/**
 * هل يمكن للشركة نشر وظيفة مميزة (featured)؟
 */
public function canPostFeatured(): bool
{
    $limit = (int) $this->getPlanFeature('featured_jobs', 0);
    if ($limit === 0)  return false;
    if ($limit === -1) return true;

    $period = now()->format('Y-m');
    $used   = SubscriptionUsage::where('company_id', $this->id)
                ->where('feature_key', 'featured_jobs')
                ->where('period', $period)
                ->value('used') ?? 0;

    return $used < $limit;
}

/**
 * هل يمكن للشركة نشر وظيفة عاجلة (urgent)؟
 */
public function canPostUrgent(): bool
{
    $value = $this->getPlanFeature('urgent_jobs', 'false');
    return in_array($value, ['true', '1', '-1']);
}

/**
 * زيادة عداد استهلاك feature.
 */
public function incrementUsage(string $featureKey): void
{
    $period = now()->format('Y-m');

    $record = SubscriptionUsage::firstOrCreate(
        [
            'company_id'  => $this->id,
            'feature_key' => $featureKey,
            'period'      => $period,
        ],
        ['used' => 0]
    );

    $record->increment('used');
}
    // ========================================================
    // Query Scopes — للاستعلامات الشائعة
    // ========================================================

    /**
     * الشركات المتحقق منها فقط.
     * الاستخدام: Company::verified()->get()
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    /**
     * الشركات النشطة.
     * الاستخدام: Company::active()->get()
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * الشركات المميزة (للصفحة الرئيسية).
     * مرتبة بعدد الوظائف النشطة.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->verified()
                     ->withCount(['jobs' => fn($q) => $q->active()])
                     ->orderByDesc('jobs_count');
    }

    // ========================================================
    // Accessors — لقراءة البيانات بصيغة محسّنة
    // ========================================================

    /**
     * رابط الشعار — يُرجع صورة افتراضية إذا لم يكن للشركة شعار.
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        return asset('images/default-company.png');
    }

    /**
     * هل اشتراك الشركة ساري؟
     */
    public function getIsSubscriptionActiveAttribute(): bool
    {
        return $this->subscription
            && $this->subscription_end
            && carbon::parse($this->subscription_end)->isFuture();
    }

    /**
     * تسمية حجم الشركة بالعربية.
     */
    public function getCompanySizeLabelAttribute(): string
    {
        return match ($this->company_size) {
            'startup' => 'ناشئة',
            'small'   => 'صغيرة',
            'medium'  => 'متوسطة',
            'large'   => 'كبيرة',
            default   => 'غير محدد',
        };
    }
    public function isSubscriptionActive(): bool
    {
        return $this->is_subscription_active;
    }
    /**
 * هل الشركة استخدمت التجربة المجانية من قبل؟
 */
    public function hasUsedTrial(): bool
    {
        return ! is_null($this->trial_used_at);
    }
    // ========================================================
    // Stripe Helpers
    // ========================================================

    /**
     * هل للشركة Stripe Customer ID؟
     */
    public function hasStripeCustomer(): bool
    {
        return ! empty($this->stripe_customer_id);
    }

    /**
     * علاقة الفواتير.
     */
    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PaymentInvoice::class)
                    ->orderByDesc('created_at');
    }

    /**
     * الفاتورة الأخيرة.
     */
    public function latestInvoice(): ?\App\Models\PaymentInvoice
    {
        return $this->invoices()->first();
    }
}
