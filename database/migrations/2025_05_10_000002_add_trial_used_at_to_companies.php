<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إضافة trial_used_at لجدول companies.
     *
     * لماذا timestamp وليس boolean؟
     *   - يحفظ متى استخدمت الشركة التجربة (audit trail)
     *   - null = لم تستخدم بعد
     *   - قيمة = استخدمتها في هذا التاريخ
     *
     * لماذا في companies وليس company_subscriptions؟
     *   - التجربة مرتبطة بالشركة ككيان، ليس باشتراك بعينه
     *   - سهولة الفحص: $company->trial_used_at بدل query
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->timestamp('trial_used_at')
                  ->nullable()
                  ->after('subscription_end')
                  ->comment('وقت استخدام التجربة المجانية — null تعني لم تُستخدم بعد');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('trial_used_at');
        });
    }
};