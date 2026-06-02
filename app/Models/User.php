<?php

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail, CanResetPasswordContract, FilamentUser
{
    use CanResetPassword, HasFactory, HasRoles, Notifiable, SoftDeletes;

    // ========================================================
    // الحقول المسموح بتعبئتها
    // ========================================================
    protected $fillable = [
        'username',
        'full_name',
        'email',
        'password',
        'phone',
        'phone_code',
        'profile_picture',
        'bio',
        'address',
        'birth_date',
        'gender',
        'linkedin_url',
        'github_url',
        'portfolio_url',
        'status',
        'is_active',
    ];

    // ========================================================
    // الحقول المخفية
    // ========================================================
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ========================================================
    // تحويل الأنواع
    // ========================================================
    protected $casts = [
        'is_active'      => 'boolean',
        'email_verified' => 'boolean',
        'email_verified_at' => 'datetime',
        'phone_verified' => 'boolean',
        'birth_date'     => 'date',
        'last_login'     => 'datetime',
        'password'       => 'hashed',
    ];

    // ========================================================
    // العلاقات
    // ========================================================

    /**
     * طلبات التوظيف التي قدّمها المستخدم.
     * مستخدم واحد → طلبات كثيرة (One-to-Many)
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * محادثات المستخدم مع الشركات.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function professionalProfile(): HasOne
    {
        return $this->hasOne(ProfessionalProfile::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(UserExperience::class)->orderBy('sort_order');
    }

    public function educations(): HasMany
    {
        return $this->hasMany(UserEducation::class)->orderBy('sort_order');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(UserProject::class)->orderBy('sort_order');
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(UserCertification::class)->orderBy('sort_order');
    }

    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class);
    }

    public function canonicalSkills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'user_skills')
            ->using(UserSkill::class)
            ->withPivot([
                'id',
                'proficiency_level',
                'proficiency_score',
                'years_experience',
                'is_featured',
                'endorsement_count',
                'source',
                'confidence_score',
                'sort_order',
                'evidence',
            ])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'user_languages')
            ->using(UserLanguage::class)
            ->withPivot(['id', 'proficiency_level', 'proficiency_score', 'is_native', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    /**
     * إجمالي الرسائل غير المقروءة.
     */
    public function getUnreadMessagesCountAttribute(): int
    {
        return $this->conversations()->sum('user_unread');
    }

    /**
     * الوظائف التي قدّم عليها المستخدم (عبر applications).
     * مستخدم ↔ وظائف (Many-to-Many عبر applications)
     */
    public function appliedJobs(): BelongsToMany
    {
        return $this->belongsToMany(Job::class, 'applications')
                    ->withPivot(['status', 'cover_letter', 'cv_path', 'resume_id', 'submitted_resume_pdf_path', 'applied_at'])
                    ->withTimestamps();
    }

    // ========================================================
    // Query Scopes
    // ========================================================

    /**
     * المستخدمون النشطون فقط.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    // ========================================================
    // Accessors
    // ========================================================

    /**
     * الاسم الظاهر — يفضّل الاسم الكامل على اسم المستخدم.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->full_name ?: $this->username;
    }

    /**
     * رابط الصورة الشخصية مع صورة افتراضية.
     */
    public function getAvatarUrlAttribute(): string
    {
        $pic = $this->profile_picture ?? $this->profile_image;

        if ($pic) {
            // دعم المسارات القديمة من النظام السابق (uploads/...)
            if (str_starts_with($pic, 'uploads/')) {
                return asset($pic);
            }
            return asset('storage/' . $pic);
        }

        return asset('images/default-avatar.png');
    }

    /**
     * هل قدّم المستخدم على وظيفة معينة؟
     */
    public function hasAppliedTo(int $jobId): bool
    {
        return $this->applications()
                    ->where('job_id', $jobId)
                    ->exists();
    }
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('admin') || $this->can('access admin panel');
    }
    public function getNameAttribute(): string
    {
        return $this->full_name ?? $this->username ?? 'Admin';
    }
    
}
