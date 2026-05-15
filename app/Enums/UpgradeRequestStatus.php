<?php

namespace App\Enums;

/**
 * حالات طلب ترقية الاشتراك.
 *
 * تدفق الحالات:
 *   pending  → approved  (الأدمن وافق → ينشئ CompanySubscription)
 *   pending  → rejected  (الأدمن رفض → يُرسل إشعار بالسبب)
 *   pending  → cancelled (الشركة سحبت الطلب)
 *
 * لماذا PHP Enum وليس DB ENUM؟
 *   - إضافة حالة جديدة = سطر PHP فقط، بدون migration
 *   - Type safety: المقارنة بـ UpgradeRequestStatus::Pending->value بدلاً من 'pending'
 *   - IDE autocomplete كامل
 *   - Laravel cast تلقائي في Model
 */
enum UpgradeRequestStatus: string
{
    case Pending   = 'pending';
    case Approved  = 'approved';
    case Rejected  = 'rejected';
    case Cancelled = 'cancelled';

    // ========================================================
    // Labels — للعرض في Filament وViews
    // ========================================================

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'قيد المراجعة',
            self::Approved  => 'مقبول',
            self::Rejected  => 'مرفوض',
            self::Cancelled => 'ملغي',
        };
    }

    // ========================================================
    // Colors — لـ Filament badges
    // ========================================================

    public function color(): string
    {
        return match($this) {
            self::Pending   => 'warning',
            self::Approved  => 'success',
            self::Rejected  => 'danger',
            self::Cancelled => 'gray',
        };
    }

    // ========================================================
    // Icons — لـ Filament actions وbadges
    // ========================================================

    public function icon(): string
    {
        return match($this) {
            self::Pending   => 'heroicon-o-clock',
            self::Approved  => 'heroicon-o-check-circle',
            self::Rejected  => 'heroicon-o-x-circle',
            self::Cancelled => 'heroicon-o-minus-circle',
        };
    }

    // ========================================================
    // State checks
    // ========================================================

    /**
     * هل الطلب قابل للمعالجة من الأدمن؟
     */
    public function isActionable(): bool
    {
        return $this === self::Pending;
    }

    /**
     * هل الطلب في حالة نهائية؟ (لا يمكن تغييره)
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::Approved, self::Rejected, self::Cancelled]);
    }

    /**
     * هل يمكن للشركة إلغاؤه؟
     */
    public function canBeCancelledByCompany(): bool
    {
        return $this === self::Pending;
    }

    // ========================================================
    // Static helpers
    // ========================================================

    /**
     * قائمة الحالات للـ Filament SelectFilter.
     */
    public static function filamentOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

    /**
     * قائمة الألوان للـ Filament badge color callback.
     */
    public static function filamentColors(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->color()])
            ->toArray();
    }
}