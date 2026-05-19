<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إضافة stripe_session_id منفصل عن payment_intent_id.
     *
     * الفرق:
     *   stripe_session_id  → Stripe Checkout Session (cs_live_xxx) — مؤقت، للـ redirect
     *   payment_intent_id  → Stripe PaymentIntent (pi_live_xxx)    — دائم، للـ refunds
     *
     * لماذا الفصل ضروري؟
     *   - Refunds تحتاج payment_intent_id الحقيقي
     *   - Idempotency في webhook يعتمد على stripe_session_id
     *   - Stripe Invoice (مستقبلاً) له ID مختلف عن كليهما
     */
    public function up(): void
    {
        Schema::table('subscription_upgrade_requests', function (Blueprint $table) {
            $table->string('stripe_session_id', 100)
                  ->nullable()
                  ->unique()
                  ->after('payment_intent_id')
                  ->comment('Stripe Checkout Session ID (cs_live_xxx)');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_upgrade_requests', function (Blueprint $table) {
            $table->dropUnique(['stripe_session_id']);
            $table->dropColumn('stripe_session_id');
        });
    }
};