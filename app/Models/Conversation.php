<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'user_id', 'job_id',
        'last_message', 'last_message_at',
        'company_unread', 'user_unread',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'company_unread'  => 'integer',
        'user_unread'     => 'integer',
    ];

    // ========================================================
    // العلاقات
    // ========================================================

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest()->limit(1);
    }

    // ========================================================
    // Scopes
    // ========================================================

    /** محادثات شركة معينة */
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /** محادثات مستخدم معين */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // ========================================================
    // Methods
    // ========================================================

    /**
     * تحديث بيانات آخر رسالة + عداد الغير مقروءة.
     */
    public function updateLastMessage(Message $message, string $senderType): void
    {
        $updates = [
            'last_message'    => mb_substr($message->body, 0, 100),
            'last_message_at' => now(),
        ];

        // زيادة عداد الغير مقروء للطرف الآخر
        if ($senderType === Company::class) {
            $updates['user_unread'] = $this->user_unread + 1;
        } else {
            $updates['company_unread'] = $this->company_unread + 1;
        }

        $this->update($updates);
    }

    /**
     * إعادة ضبط عداد الغير مقروء عند فتح المحادثة.
     */
    public function markReadFor(string $readerType): void
    {
        if ($readerType === Company::class) {
            $this->update(['company_unread' => 0]);
        } else {
            $this->update(['user_unread' => 0]);
        }
    }
}
