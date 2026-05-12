<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionUsage extends Model
{
     protected $table = 'subscription_usage';
    protected $fillable = [
        'company_id',
        'feature_key',
        'used',
        'period',
    ];

    protected $casts = [
        'used' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}