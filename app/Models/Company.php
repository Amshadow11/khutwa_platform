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
}
