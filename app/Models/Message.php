<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_id',
        'body',
        'attachment_path',
        'attachment_name',
        'attachment_type',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    // ========================================================
    // العلاقات
    // ========================================================

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * المرسل — يمكن أن يكون Company أو User.
     * Polymorphic relationship.
     */
    public function sender(): MorphTo
    {
        return $this->morphTo();
    }

    // ========================================================
    // Accessors
    // ========================================================

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment_path
            ? asset('storage/' . $this->attachment_path)
            : null;
    }

    public function getIsFromCompanyAttribute(): bool
    {
        return $this->sender_type === Company::class;
    }

    // ========================================================
    // Methods
    // ========================================================

    public function markAsRead(): void
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }
}
