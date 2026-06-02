<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIUsageLog extends Model
{
    protected $table = 'ai_usage_logs';

    protected $fillable = [
        'user_id',
        'company_id',
        'feature',
        'period',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'cost_usd',
        'duration_ms',
        'status',
        'error_message',
    ];

    protected $casts = [
        'prompt_tokens'     => 'integer',
        'completion_tokens' => 'integer',
        'total_tokens'      => 'integer',
        'cost_usd'          => 'decimal:6',
        'duration_ms'       => 'integer',
    ];

    // ========================================================
    // العلاقات
    // ========================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // ========================================================
    // Scopes
    // ========================================================

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForFeature(Builder $query, string $feature): Builder
    {
        return $query->where('feature', $feature);
    }

    public function scopeForPeriod(Builder $query, string $period): Builder
    {
        return $query->where('period', $period);
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', 'success');
    }

    // ========================================================
    // Static Helpers
    // ========================================================

    /**
     * عدد استخدامات feature معينة هذا الشهر لمستخدم.
     */
    public static function monthlyUsageForUser(int $userId, string $feature): int
    {
        return static::where('user_id', $userId)
            ->where('feature', $feature)
            ->where('period', now()->format('Y-m'))
            ->where('status', 'success')
            ->count();
    }

    /**
     * عدد استخدامات feature معينة هذا الشهر لشركة.
     */
    public static function monthlyUsageForCompany(int $companyId, string $feature): int
    {
        return static::where('company_id', $companyId)
            ->where('feature', $feature)
            ->where('period', now()->format('Y-m'))
            ->where('status', 'success')
            ->count();
    }

    /**
     * تسجيل استخدام ناجح.
     */
    public static function logSuccess(
        string $feature,
        string $model,
        int $promptTokens,
        int $completionTokens,
        int $durationMs,
        ?int $userId = null,
        ?int $companyId = null,
    ): static {
        $costs       = config("ai.costs.{$model}", ['input' => 0, 'output' => 0]);
        $costUsd     = ($promptTokens / 1000 * $costs['input'])
                     + ($completionTokens / 1000 * $costs['output']);

        return static::create([
            'user_id'           => $userId,
            'company_id'        => $companyId,
            'feature'           => $feature,
            'period'            => now()->format('Y-m'),
            'model'             => $model,
            'prompt_tokens'     => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens'      => $promptTokens + $completionTokens,
            'cost_usd'          => $costUsd,
            'duration_ms'       => $durationMs,
            'status'            => 'success',
        ]);
    }

    /**
     * تسجيل استخدام فاشل.
     */
    public static function logFailure(
        string $feature,
        string $model,
        string $errorMessage,
        ?int $userId = null,
        ?int $companyId = null,
    ): static {
        return static::create([
            'user_id'       => $userId,
            'company_id'    => $companyId,
            'feature'       => $feature,
            'period'        => now()->format('Y-m'),
            'model'         => $model,
            'status'        => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}