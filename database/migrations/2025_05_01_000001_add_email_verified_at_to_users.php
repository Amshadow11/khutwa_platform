<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * إضافة عمود email_verified_at لجدول users.
     *
     * نستخدم نمط Expand-Contract:
     *   - نُضيف العمود الجديد (email_verified_at) أولاً
     *   - نحتفظ بـ email_verified القديم للتوافق
     *   - نحوّل البيانات القديمة: email_verified=true → email_verified_at = now()
     *
     * لماذا timestamp وليس boolean؟
     *   - Laravel MustVerifyEmail يعتمد على email_verified_at
     *   - يحفظ وقت التحقق (مفيد للتدقيق والإحصاء)
     *   - null = غير متحقق / قيمة = متحقق ووقت التحقق
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // إضافة العمود الجديد بعد email مباشرة
            $table->timestamp('email_verified_at')
                  ->nullable()
                  ->after('email');
        });

        // نقل البيانات القديمة:
        // المستخدمون الذين email_verified = true → نعطيهم email_verified_at = created_at
        DB::table('users')
            ->where('email_verified', true)
            ->update(['email_verified_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_verified_at');
        });
    }
};