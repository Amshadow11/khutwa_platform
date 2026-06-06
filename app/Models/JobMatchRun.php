<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JobMatchRun extends Model
{
    use HasFactory;

    public const STATUS_QUEUED = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'job_id',
        'company_id',
        'initiated_by_type',
        'initiated_by_id',
        'status',
        'provider',
        'model',
        'matching_version',
        'job_snapshot_hash',
        'applications_total',
        'applications_processed',
        'applications_reused',
        'applications_failed',
        'started_at',
        'completed_at',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'matching_version' => 'integer',
        'applications_total' => 'integer',
        'applications_processed' => 'integer',
        'applications_reused' => 'integer',
        'applications_failed' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function initiatedBy(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'initiated_by_type', 'initiated_by_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(JobApplicationMatch::class);
    }
}
