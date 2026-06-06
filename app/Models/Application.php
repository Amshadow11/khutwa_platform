<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\URL;

class Application extends Model
{
    use HasFactory, SoftDeletes;

    // ========================================================
    // الحالات المتاحة
    // ========================================================
    const STATUS_PENDING     = 'pending';
    const STATUS_VIEWED      = 'viewed';
    const STATUS_SHORTLISTED = 'shortlisted';
    const STATUS_INTERVIEW   = 'interview';
    const STATUS_ACCEPTED    = 'accepted';
    const STATUS_REJECTED    = 'rejected';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_VIEWED,
        self::STATUS_SHORTLISTED,
        self::STATUS_INTERVIEW,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
    ];

    // ========================================================
    // الحقول المسموح بتعبئتها
    // ========================================================
    protected $fillable = [
        'job_id',
        'user_id',
        'resume_id',
        'cover_letter',
        'cv_path',
        'resume_snapshot',
        'resume_snapshot_hash',
        'resume_snapshot_version',
        'resume_snapshot_created_at',
        'submitted_resume_pdf_path',
        'applicant_name',
        'applicant_email',
        'applicant_phone',
        'status',
        'applied_at',
    ];

    // ========================================================
    // تحويل الأنواع
    // ========================================================
    protected $casts = [
        'applied_at'        => 'datetime',
        'status_updated_at' => 'datetime',
        'resume_snapshot' => 'array',
        'resume_snapshot_version' => 'integer',
        'resume_snapshot_created_at' => 'datetime',
    ];

    // ========================================================
    // العلاقات
    // ========================================================

    /**
     * الوظيفة التي تمّ التقديم عليها.
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * المستخدم الذي تقدّم.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    /**
     * تاريخ تغييرات حالة الطلب.
     * One-to-Many: طلب واحد → سجلات تاريخ كثيرة
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class)
                    ->orderBy('changed_at', 'asc');
    }

    public function atsNotes(): HasMany
    {
        return $this->hasMany(ApplicationNote::class)->latest();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ApplicationReview::class)->latest();
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(ApplicationInterview::class)->latest('scheduled_at');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ApplicationActivity::class)->latest('occurred_at');
    }

    public function aiMatches(): HasMany
    {
        return $this->hasMany(JobApplicationMatch::class)->latest('evaluated_at');
    }

    public function latestAiMatch(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(JobApplicationMatch::class)->latestOfMany('evaluated_at');
    }

    // ========================================================
    // Query Scopes
    // ========================================================

    /**
     * الطلبات حسب الحالة.
     * الاستخدام: Application::withStatus('pending')->get()
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * الطلبات الجديدة غير المفتوحة (للشركة).
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * أحدث الطلبات (للـ Dashboard).
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('applied_at', '>=', now()->subDays($days));
    }

    // ========================================================
    // Accessors
    // ========================================================

    /**
     * اسم الحالة بالعربية.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING     => 'قيد المراجعة',
            self::STATUS_VIEWED      => 'تمت المشاهدة',
            self::STATUS_SHORTLISTED => 'في القائمة المختصرة',
            self::STATUS_INTERVIEW   => 'دُعي للمقابلة',
            self::STATUS_ACCEPTED    => 'مقبول',
            self::STATUS_REJECTED    => 'مرفوض',
            default                  => 'غير محدد',
        };
    }

    /**
     * لون badge الحالة (Bootstrap classes).
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING     => 'warning',
            self::STATUS_VIEWED      => 'info',
            self::STATUS_SHORTLISTED => 'primary',
            self::STATUS_INTERVIEW   => 'purple',
            self::STATUS_ACCEPTED    => 'success',
            self::STATUS_REJECTED    => 'danger',
            default                  => 'secondary',
        };
    }

    /**
     * رابط ملف CV مع دعم المسارات القديمة.
     */
    public function getCvUrlAttribute(): ?string
    {
        if (! $this->cv_path) {
            return null;
        }
        // دعم المسارات القديمة من النظام السابق
        if (str_starts_with($this->cv_path, 'uploads/')) {
            return asset($this->cv_path);
        }

        return URL::temporarySignedRoute(
            'secure-files.applications.cv',
            now()->addMinutes(config('files.signed_url_ttl_minutes')),
            ['application' => $this->id]
        );
    }

    public function getSubmittedResumePdfUrlAttribute(): ?string
    {
        if ($this->submitted_resume_pdf_path) {
            return URL::temporarySignedRoute(
                'secure-files.applications.submitted-resume',
                now()->addMinutes(config('files.signed_url_ttl_minutes')),
                ['application' => $this->id]
            );
        }

        return $this->cv_url;
    }

    public function getSnapshotIdentityAttribute(): array
    {
        return $this->resume_snapshot['identity'] ?? [];
    }

    public function getCandidateNameAttribute(): string
    {
        return $this->snapshot_identity['name']
            ?? $this->applicant_name
            ?? $this->user?->display_name
            ?? 'متقدم';
    }

    public function getCandidateEmailAttribute(): ?string
    {
        return $this->snapshot_identity['email']
            ?? $this->applicant_email
            ?? $this->user?->email;
    }

    public function getCandidatePhoneAttribute(): ?string
    {
        return $this->snapshot_identity['phone']
            ?? $this->applicant_phone
            ?? $this->user?->phone;
    }

    public function getCandidateHeadlineAttribute(): ?string
    {
        return $this->snapshot_identity['headline']
            ?? $this->snapshot_identity['current_title']
            ?? null;
    }

    public function getCandidateLocationAttribute(): ?string
    {
        $parts = collect([
            $this->snapshot_identity['city'] ?? null,
            $this->snapshot_identity['country'] ?? null,
        ])->filter();

        return $parts->isNotEmpty()
            ? $parts->implode(', ')
            : $this->user?->address;
    }

    public function getSnapshotSkillsSummaryAttribute(): string
    {
        return collect($this->resume_snapshot['skills'] ?? [])
            ->pluck('name')
            ->filter()
            ->take(8)
            ->implode(', ');
    }
}
