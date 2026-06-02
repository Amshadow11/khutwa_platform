<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;

class Resume extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'template_id',
        'parent_resume_id',
        'target_job_id',
        'title',
        'slug',
        'visibility',
        'public_token',
        'private_share_token',
        'is_default',
        'version_number',
        'locale',
        'direction',
        'tailored_summary',
        'settings',
        'profile_snapshot',
        'snapshot_version',
        'snapshot_hash',
        'snapshot_created_at',
        'completeness_score',
        'published_at',
        'last_generated_at',
        'generated_pdf_path',
        'pdf_status',
        'pdf_error',
        'render_metadata',
        'seo_metadata',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'version_number' => 'integer',
        'settings' => 'array',
        'profile_snapshot' => 'array',
        'snapshot_version' => 'integer',
        'snapshot_created_at' => 'datetime',
        'completeness_score' => 'decimal:2',
        'published_at' => 'datetime',
        'last_generated_at' => 'datetime',
        'render_metadata' => 'array',
        'seo_metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ResumeTemplate::class, 'template_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_resume_id');
    }

    public function targetJob(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'target_job_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ResumeSection::class)->orderBy('sort_order');
    }

    public function getIsPublicAttribute(): bool
    {
        return $this->visibility === 'public';
    }

    public function getIsShareableAttribute(): bool
    {
        return in_array($this->visibility, ['public', 'unlisted', 'signed'], true);
    }

    public function shareUrl(): ?string
    {
        if (! $this->is_shareable || ! $this->public_token) {
            return null;
        }

        if ($this->visibility === 'signed') {
            return URL::temporarySignedRoute('resumes.public.show', now()->addDays(14), ['resume' => $this->public_token]);
        }

        return route('resumes.public.show', $this->public_token);
    }

    public function downloadUrl(): ?string
    {
        if (! $this->is_shareable || ! $this->public_token) {
            return null;
        }

        if ($this->visibility === 'signed') {
            return URL::temporarySignedRoute('resumes.public.download', now()->addDays(14), ['resume' => $this->public_token]);
        }

        return route('resumes.public.download', $this->public_token);
    }
}
