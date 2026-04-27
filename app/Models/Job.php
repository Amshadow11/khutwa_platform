<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Job extends Model
{
    use HasFactory, SoftDeletes;

    // ========================================================
    // الحقول المسموح بتعبئتها
    // ========================================================
    protected $fillable = [
        'company_id',
        'title',
        'description',
        'requirements',
        'benefits',
        'category',
        'job_type',
        'experience_level',
        'location',
        'remote_work',
        'salary',
        'salary_range',
        'status',
        'is_active',
        'featured',
        'urgent',
        'deadline',
        'post_date',
    ];

    // ========================================================
    // تحويل الأنواع
    // ========================================================
    protected $casts = [
        'is_active'   => 'boolean',
        'featured'    => 'boolean',
        'urgent'      => 'boolean',
        'remote_work' => 'boolean',
        'deadline'    => 'date',
        'post_date'   => 'datetime',
        'views'       => 'integer',
    ];

    // ========================================================
    // العلاقات
    // ========================================================

    /**
     * الشركة المالكة للوظيفة.
     * وظيفة → شركة واحدة (Many-to-One)
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * طلبات التقديم على هذه الوظيفة.
     * وظيفة → طلبات كثيرة (One-to-Many)
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * المتقدمون على الوظيفة (عبر applications).
     */
    public function applicants(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'applications')
                    ->withPivot(['status', 'cover_letter', 'applied_at'])
                    ->withTimestamps();
    }

    // ========================================================
    // Query Scopes
    // ========================================================

    /**
     * الوظائف النشطة فقط (للصفحات العامة).
     * الاستخدام: Job::active()->get()
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
                     ->where('status', 'active')
                     ->where(function ($q) {
                         $q->whereNull('deadline')
                           ->orWhere('deadline', '>=', now()->toDateString());
                     });
    }

    /**
     * الوظائف المميزة للصفحة الرئيسية.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->active()->where('featured', true);
    }

    /**
     * وظائف شركة معينة.
     * الاستخدام: Job::forCompany($companyId)->get()
     */
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * البحث النصي في العنوان والوصف.
     * الاستخدام: Job::search('مبرمج')->get()
     */
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
              ->orWhere('description', 'like', "%{$keyword}%")
              ->orWhere('requirements', 'like', "%{$keyword}%");
        });
    }

    /**
     * فلترة متعددة دفعة واحدة.
     * الاستخدام: Job::filter($request->only(['keyword','location','job_type']))->paginate()
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['keyword'] ?? null, fn($q, $k) => $q->search($k))
            ->when($filters['location'] ?? null, fn($q, $l) => $q->where('location', $l))
            ->when($filters['job_type'] ?? null, fn($q, $t) => $q->where('job_type', $t))
            ->when($filters['category'] ?? null, fn($q, $c) => $q->where('category', $c))
            ->when($filters['experience_level'] ?? null, fn($q, $e) => $q->where('experience_level', $e))
            ->when(isset($filters['remote_work']), fn($q) => $q->where('remote_work', true))
            ->when(isset($filters['urgent']), fn($q) => $q->where('urgent', true));
    }

    // ========================================================
    // Accessors
    // ========================================================

    /**
     * تسمية نوع الوظيفة بالعربية.
     */
    public function getJobTypeLabelAttribute(): string
    {
        return match ($this->job_type) {
            'full_time' => 'دوام كامل',
            'part_time' => 'دوام جزئي',
            'remote'    => 'عن بُعد',
            'contract'  => 'عقد',
            'freelance' => 'عمل حر',
            default     => $this->job_type ?? 'غير محدد',
        };
    }

    /**
     * تسمية الفئة بالعربية.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'job'      => 'وظيفة',
            'training' => 'تدريب',
            default    => 'وظيفة',
        };
    }

    /**
     * هل انتهت مدة الوظيفة؟
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->deadline && $this->deadline->isPast();
    }

    /**
     * كم يوم متبقي على انتهاء الوظيفة؟
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (! $this->deadline) {
            return null;
        }
        $days = now()->diffInDays($this->deadline, false);
        return $days >= 0 ? (int) $days : 0;
    }

    // ========================================================
    // Methods
    // ========================================================

    /**
     * زيادة عداد المشاهدات بشكل آمن (Atomic increment — لا race conditions).
     */
    public function incrementViews(): void
    {
        $this->increment('views');
    }
}
