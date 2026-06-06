<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApplicationStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'application_status_history';

    protected $fillable = [
        'application_id',
        'from_status',
        'status',
        'note',
        'actor_type',
        'actor_id',
        'transition_key',
        'metadata',
        'changed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'changed_at' => 'datetime',
    ];

    // ========================================================
    // العلاقات
    // ========================================================

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    // ========================================================
    // Accessors
    // ========================================================

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'     => 'قيد المراجعة',
            'viewed'      => 'تمت المشاهدة',
            'shortlisted' => 'في القائمة المختصرة',
            'interview'   => 'دُعي للمقابلة',
            'accepted'    => 'مقبول',
            'rejected'    => 'مرفوض',
            default       => $this->status,
        };
    }
}
