<?php

namespace App\Actions\Stripe;

use App\Actions\Subscription\RequestUpgradeAction;
use App\Models\Company;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionUpgradeRequest;
use App\Services\StripeService;
use App\Services\SubscriptionService;
use Stripe\Checkout\Session;

class CreateCheckoutSessionAction
{
    public function __construct(
        private readonly StripeService $stripeService,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    /**
     * إنشاء Stripe Checkout Session لطلب ترقية.
     *
     * التدفق:
     *   1. إنشاء upgrade_request (pending) عبر RequestUpgradeAction
     *   2. إنشاء Stripe Checkout Session
     *   3. حفظ stripe_session_id في الطلب
     *   4. إعادة الـ checkout_url للـ Controller
     *
     * عند نجاح الدفع:
     *   Stripe webhook → HandleSuccessfulPaymentAction
     *     → يُملأ payment_intent_id الحقيقي (pi_live_xxx)
     *     → ApproveSubscriptionAction
     *
     * @throws \RuntimeException من RequestUpgradeAction إذا فشلت الـ Guards
     * @throws \InvalidArgumentException إذا الخطة بدون stripe_price_id
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function execute(
        Company $company,
        SubscriptionPlan $plan,
        int $months = 1,
        ?string $notes = null
    ): array {
        // ── Guard: الخطة لها Stripe Price ───────────────────────
        if (! $plan->hasStripePrice()) {
            throw new \InvalidArgumentException(
                "خطة \"{$plan->name}\" لا تدعم الدفع الإلكتروني. أضف stripe_price_id أولاً."
            );
        }

        // ── إنشاء upgrade_request (pending) ─────────────────────
        // RequestUpgradeAction تتحقق من:
        //   - لا pending request موجود
        //   - الخطة مختلفة عن الحالية
        //   - الخطة ليست مجانية
        $upgradeRequest = app(RequestUpgradeAction::class)->execute(
            $company, $plan, $months, $notes
        );

        // تحديد payment_method كـ stripe
        $upgradeRequest->update(['payment_method' => 'stripe']);

        // ── إنشاء Stripe Checkout Session ───────────────────────
        $session = $this->stripeService->createCheckoutSession(
            company:        $company,
            plan:           $plan,
            upgradeRequest: $upgradeRequest,
            months:         $months,
            successUrl:     route('stripe.checkout.success'),
            cancelUrl:      route('company.subscription.index'),
        );

        // ── حفظ stripe_session_id (cs_live_xxx) ─────────────────
        // payment_intent_id يبقى NULL حتى يصل الـ webhook
        // ونعرف الـ PaymentIntent الحقيقي (pi_live_xxx)
        $upgradeRequest->update([
            'stripe_session_id' => $session->id,
        ]);

        return [
            'session'         => $session,
            'checkout_url'    => $session->url,
            'upgrade_request' => $upgradeRequest,
        ];
    }
}