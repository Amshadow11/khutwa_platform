<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول الفواتير — تاريخ كامل لجميع المدفوعات.
     *
     * يُملأ من:
     *   - Stripe Webhook (invoice.payment_succeeded)
     *   - Manual payments من Admin
     *
     * يدعم مستقبلاً:
     *   - تحميل PDF
     *   - Recurring billing
     *   - Refunds
     */
    public function up(): void
    {
        Schema::create('payment_invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                  ->constrained('companies')
                  ->cascadeOnDelete();

            // الاشتراك المرتبط بهذه الفاتورة (nullable — قبل إنشاء الاشتراك)
            $table->foreignId('subscription_id')
                  ->nullable()
                  ->constrained('company_subscriptions')
                  ->nullOnDelete();

            // طلب الترقية المرتبط (للـ audit trail)
            $table->foreignId('upgrade_request_id')
                  ->nullable()
                  ->constrained('subscription_upgrade_requests')
                  ->nullOnDelete();

            // ========================================================
            // Stripe Fields
            // ========================================================

            $table->string('stripe_invoice_id', 100)->nullable()->unique();
            $table->string('stripe_payment_intent_id', 100)->nullable()->unique();
            $table->string('stripe_session_id', 100)->nullable()->unique();

            // ========================================================
            // بيانات الفاتورة
            // ========================================================

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');

            // paid, open, void, uncollectible, draft
            $table->string('status', 30)->default('open');

            $table->string('description', 500)->nullable();

            // روابط Stripe
            $table->string('invoice_url', 500)->nullable();   // رابط الفاتورة online
            $table->string('invoice_pdf', 500)->nullable();   // رابط PDF

            // ========================================================
            // Timestamps الأحداث
            // ========================================================

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('voided_at')->nullable();

            // ========================================================
            // Payment Method المستخدمة
            // ========================================================

            $table->string('payment_method_type', 50)->nullable(); // card, bank_transfer
            $table->string('payment_method_last4', 4)->nullable(); // آخر 4 أرقام
            $table->string('payment_method_brand', 30)->nullable(); // visa, mastercard

            $table->timestamps();

            // Indexes
            $table->index('company_id');
            $table->index('status');
            $table->index('paid_at');
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_invoices');
    }
};