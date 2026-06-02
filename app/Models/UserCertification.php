<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCertification extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'issuing_organization',
        'credential_id',
        'credential_url',
        'issued_at',
        'expires_at',
        'does_not_expire',
        'verification_status',
        'skills_snapshot',
        'sort_order',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'expires_at' => 'date',
        'does_not_expire' => 'boolean',
        'skills_snapshot' => 'array',
        'sort_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
