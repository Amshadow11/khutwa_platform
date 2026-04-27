<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

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
        'skills',
        'experience',
        'education',
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
        return $this->hasMany(\App\Models\Conversation::class);
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
    public function appliedJobs(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Job::class, 'applications')
                    ->withPivot(['status', 'cover_letter', 'cv_path', 'applied_at'])
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
}
