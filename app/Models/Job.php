<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\Searchable;
use Illuminate\Support\Carbon;

class Job extends Model
{
    use HasFactory, Searchable, SoftDeletes;

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

    public function matchRuns(): HasMany
    {
        return $this->hasMany(JobMatchRun::class);
    }

    public function applicationMatches(): HasMany
    {
        return $this->hasMany(JobApplicationMatch::class);
    }

    /**
     * المتقدمون على الوظيفة (عبر applications).
     */
    public function applicants(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'applications')
                    ->withPivot(['status', 'cover_letter', 'cv_path', 'resume_id', 'submitted_resume_pdf_path', 'applied_at'])
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
        return $this->deadline instanceof Carbon && $this->deadline->isPast();
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

    public function searchableAs(): string
    {
        return config('scout.prefix') . 'jobs';
    }

    public function shouldBeSearchable(): bool
    {
        return $this->isPubliclySearchable();
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing('company:id,company_name,is_verified,status');

        return [
            'id' => (int) $this->id,
            'company_id' => (int) $this->company_id,
            'company_name' => $this->company?->company_name,
            'company_verified' => (bool) $this->company?->is_verified,
            'title' => $this->title,
            'description' => $this->description,
            'requirements' => $this->requirements,
            'benefits' => $this->benefits,
            'category' => $this->category,
            'job_type' => $this->job_type,
            'experience_level' => $this->experience_level,
            'location' => $this->location,
            'remote_work' => (bool) $this->remote_work,
            'urgent' => (bool) $this->urgent,
            'featured' => (bool) $this->featured,
            'status' => $this->status,
            'is_active' => (bool) $this->is_active,
            'deadline' => $this->deadline?->copy()->startOfDay()->timestamp,
            'post_date' => $this->post_date?->timestamp,
            'created_at' => $this->created_at?->timestamp,
        ];
    }

    private function isPubliclySearchable(): bool
    {
        return (bool) $this->is_active
            && $this->status === 'active'
            && (! $this->deadline instanceof Carbon || $this->deadline->isToday() || $this->deadline->isFuture());
    }
}
