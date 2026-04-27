<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'application_status_history';

    protected $fillable = [
        'application_id',
        'status',
        'note',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    // ========================================================
    // العلاقات
    // ========================================================

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
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
