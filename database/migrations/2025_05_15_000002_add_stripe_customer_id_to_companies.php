<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إضافة stripe_customer_id لجدول companies.
     *
     * كل شركة تدفع = Customer في Stripe.
     * يُنشأ تلقائياً عند أول Checkout Session.
     *
     * الفائدة:
     *   - تاريخ المدفوعات في Stripe Dashboard
     *   - دعم saved payment methods مستقبلاً
     *   - ربط الـ invoices بالشركة
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('stripe_customer_id', 100)
                  ->nullable()
                  ->after('trial_used_at')
                  ->comment('Stripe Customer ID — يُنشأ تلقائياً عند أول دفع');

            $table->index('stripe_customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['stripe_customer_id']);
            $table->dropColumn('stripe_customer_id');
        });
    }
};