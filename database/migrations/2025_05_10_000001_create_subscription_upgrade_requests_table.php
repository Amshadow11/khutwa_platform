<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول طلبات ترقية الاشتراك.
     *
     * مفصول عن company_subscriptions لأن:
     *   - الطلب ليس اشتراكاً فعلياً
     *   - يحفظ audit trail مستقل (من طلب، من وافق، من رفض)
     *   - يستوعب Payment Gateways مستقبلاً (payment_intent_id)
     *   - يتيح analytics (conversion rate، وقت الموافقة...)
     *
     * تدفق الحالات:
     *   pending → approved → ينشئ CompanySubscription جديد
     *   pending → rejected → يُرسل إشعار بالسبب
     *   pending → cancelled → الشركة سحبت الطلب
     *
     * status كـ string وليس ENUM:
     *   - إضافة حالة جديدة لا تحتاج migration
     *   - يُكمَّل بـ PHP Enum class (UpgradeRequestStatus)
     */
    public function up(): void
    {
        Schema::create('subscription_upgrade_requests', function (Blueprint $table) {
            $table->id();

            // الشركة الطالبة
            $table->foreignId('company_id')
                  ->constrained('companies')
                  ->cascadeOnDelete();

            // الخطة الحالية للشركة وقت الطلب (null = كانت على free بدون اشتراك)
            $table->foreignId('from_plan_id')
                  ->nullable()
                  ->constrained('subscription_plans')
                  ->nullOnDelete();

            // الخطة المطلوبة
            $table->foreignId('to_plan_id')
                  ->constrained('subscription_plans')
                  ->restrictOnDelete(); // لا تحذف الخطة إذا فيها طلبات

            // مدة الاشتراك المطلوبة بالأشهر (1، 3، 6، 12)
            $table->unsignedTinyInteger('months')->default(1);

            // المبلغ المتوقع
            $table->decimal('amount', 10, 2)->default(0);

            // الحالة — string وليس ENUM للمرونة مع PHP Enum
            // القيم: pending, approved, rejected, cancelled
            $table->string('status', 20)->default('pending');

            // ملاحظات الشركة عند الطلب
            $table->text('notes')->nullable();

            // ========================================================
            // بيانات المعالجة
            // ========================================================

            // الأدمن الذي وافق
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // الأدمن الذي رفض
            $table->foreignId('rejected_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // ملاحظات الأدمن الداخلية (لا تُرسل للشركة)
            $table->text('admin_notes')->nullable();

            // سبب الرفض — يُرسل للشركة في إشعار الرفض
            $table->text('rejection_reason')->nullable();

            // ========================================================
            // Timestamps للأحداث
            // ========================================================

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // انتهاء صلاحية الطلب — طلب قديم لم يُعالَج
            // null = لا ينتهي تلقائياً
            $table->timestamp('expires_at')->nullable();

            // ========================================================
            // Payment Gateway (مستقبلاً — Stripe / PayPal)
            // ========================================================

            // Stripe PaymentIntent ID
            $table->string('payment_intent_id', 255)->nullable();

            // طريقة الدفع المختارة
            $table->string('payment_method', 50)->nullable();

            // مرجع المعاملة بعد الدفع
            $table->string('payment_reference', 255)->nullable();

            // ========================================================
            // النتيجة — بعد الموافقة
            // ========================================================

            // رقم الاشتراك الذي أُنشئ بعد الموافقة
            // nullable لأنه يُملأ بعد activate()
            $table->foreignId('resulting_subscription_id')
                  ->nullable()
                  ->constrained('company_subscriptions')
                  ->nullOnDelete();

            $table->timestamps();

            // ========================================================
            // Indexes
            // ========================================================

            $table->index('company_id');
            $table->index('status');
            $table->index(['company_id', 'status']); // أكثر query شائعة
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_upgrade_requests');
    }
};