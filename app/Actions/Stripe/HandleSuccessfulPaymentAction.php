<?php

namespace App\Actions\Stripe;

use App\Actions\Subscription\ApproveSubscriptionAction;
use App\Models\Company;
use App\Models\PaymentInvoice;
use App\Models\SubscriptionUpgradeRequest;
use App\Notifications\PaymentReceived;
use App\Services\StripeService;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;

class HandleSuccessfulPaymentAction
{
    public function __construct(
        private readonly StripeService $stripeService,
        private readonly ApproveSubscriptionAction $approveAction,
    ) {}

    /**
     * معالجة دفع ناجح من Stripe Webhook.
     *
     * يُستدعى من: WebhookController::handleCheckoutCompleted()
     *
     * الخطوات:
     *   1. جلب UpgradeRequest عبر stripe_session_id (idempotency key)
     *   2. التحقق لم يُعالَج من قبل
     *   3. تحديث payment_intent_id بالقيمة الحقيقية (pi_live_xxx)
     *   4. ApproveSubscriptionAction (نفس Manual Flow)
     *   5. إنشاء PaymentInvoice
     *   6. إشعار الشركة
     */
    public function execute(Session $stripeSession): void
    {
        // ── جلب الطلب بـ stripe_session_id ──────────────────────
        // نستخدم stripe_session_id كـ idempotency key — فريد ومضمون
        $upgradeRequest = SubscriptionUpgradeRequest::with(['company', 'toPlan'])
            ->where('stripe_session_id', $stripeSession->id)
            ->first();

        // Fallback: جرّب metadata إذا stripe_session_id غير موجود
        if (! $upgradeRequest) {
            $upgradeRequestId = $stripeSession->metadata->upgrade_request_id ?? null;
            if ($upgradeRequestId) {
                $upgradeRequest = SubscriptionUpgradeRequest::with(['company', 'toPlan'])
                    ->find($upgradeRequestId);
            }
        }

        if (! $upgradeRequest) {
            Log::error('Stripe Webhook: UpgradeRequest غير موجود', [
                'session_id'  => $stripeSession->id,
                'metadata'    => $stripeSession->metadata->toArray(),
            ]);
            return;
        }

        // ── Idempotency Guard ────────────────────────────────────
        // إذا الطلب أُعتمد بالفعل → Stripe أرسل الـ webhook مرتين → تجاهل
        if (! $upgradeRequest->isPending()) {
            Log::info('Stripe Webhook: الطلب مُعالَج مسبقاً — تجاهل', [
                'upgrade_request_id' => $upgradeRequest->id,
                'status'             => $upgradeRequest->status->value,
                'session_id'         => $stripeSession->id,
            ]);
            return;
        }

        $company       = $upgradeRequest->company;
        $amountPaid    = $this->stripeService->fromCents($stripeSession->amount_total);

        // ── تحديث payment_intent_id الحقيقي (pi_live_xxx) ───────
        // الآن نعرف الـ PaymentIntent الحقيقي من Stripe Session
        // هذا هو الـ ID المطلوب لـ Refunds والـ dispute management
        $upgradeRequest->update([
            'payment_intent_id'  => $stripeSession->payment_intent,
            'payment_reference'  => $stripeSession->id,
            'amount'             => $amountPaid,
        ]);

        // ── ApproveSubscriptionAction (نفس Manual Flow تماماً) ───
        $this->approveAction->execute(
            upgradeRequest: $upgradeRequest,
            approvedBy:     null, // تلقائي من Stripe
            adminNotes:     'تمت الموافقة تلقائياً بعد نجاح الدفع عبر Stripe',
            options:        [
                'payment_method'    => 'stripe',
                'payment_reference' => $stripeSession->id,
                'amount_paid'       => $amountPaid,
            ]
        );

        // ── إنشاء PaymentInvoice ─────────────────────────────────
        $upgradeRequest->refresh();
        $subscription = $upgradeRequest->resultingSubscription;

        $invoice = PaymentInvoice::create([
            'company_id'               => $company->id,
            'subscription_id'          => $subscription?->id,
            'upgrade_request_id'       => $upgradeRequest->id,
            // كلا الـ IDs محفوظان بشكل صحيح ومنفصلان
            'stripe_session_id'        => $stripeSession->id,          // cs_live_xxx
            'stripe_payment_intent_id' => $stripeSession->payment_intent, // pi_live_xxx
            'amount'                   => $amountPaid,
            'currency'                 => strtoupper($stripeSession->currency),
            'status'                   => 'paid',
            'description'              => "اشتراك {$upgradeRequest->toPlan->name} لمدة {$upgradeRequest->months} شهر",
            'paid_at'                  => now(),
        ]);

        // ── إشعار الشركة ────────────────────────────────────────
        $company->notify(new PaymentReceived($invoice));

        Log::info('Stripe Webhook: تم تفعيل الاشتراك بنجاح', [
            'company_id'         => $company->id,
            'upgrade_request_id' => $upgradeRequest->id,
            'subscription_id'    => $subscription?->id,
            'stripe_session_id'  => $stripeSession->id,
            'payment_intent_id'  => $stripeSession->payment_intent,
            'amount'             => $invoice->amount,
        ]);
    }
}