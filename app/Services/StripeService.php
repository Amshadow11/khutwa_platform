<?php

namespace App\Services;

use App\Models\Company;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionUpgradeRequest;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\Webhook;

/**
 * StripeService — كل التعامل مع Stripe SDK في مكان واحد.
 *
 * لا تضع Stripe::setApiKey في أي مكان آخر.
 * لا تستدعِ \Stripe\... مباشرة من Controllers أو Actions.
 *
 * هذه الـ Service مسؤولة عن:
 *   - إنشاء / جلب Stripe Customers
 *   - إنشاء Checkout Sessions
 *   - التحقق من Webhook signatures
 *   - جلب Sessions و PaymentIntents
 */
class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    // ========================================================
    // Customer Management
    // ========================================================

    /**
     * جلب أو إنشاء Stripe Customer للشركة.
     *
     * إذا الشركة لديها stripe_customer_id → نجلبه من Stripe
     * إذا لا → ننشئ Customer جديد ونحفظ الـ ID
     */
    public function getOrCreateCustomer(Company $company): Customer
    {
        if ($company->hasStripeCustomer()) {
            return Customer::retrieve($company->stripe_customer_id);
        }

        $customer = Customer::create([
            'name'     => $company->company_name,
            'email'    => $company->email,
            'metadata' => [
                'company_id'   => $company->id,
                'company_name' => $company->company_name,
            ],
        ]);

        // حفظ الـ ID في قاعدة البيانات
        $company->update(['stripe_customer_id' => $customer->id]);

        return $customer;
    }

    // ========================================================
    // Checkout Session
    // ========================================================

    /**
     * إنشاء Stripe Checkout Session.
     *
     * يُعيد URL يُوجَّه إليه المستخدم للدفع.
     *
     * @param Company $company الشركة الدافعة
     * @param SubscriptionPlan $plan الخطة المختارة
     * @param SubscriptionUpgradeRequest $upgradeRequest طلب الترقية
     * @param int $months عدد الأشهر
     * @param string $successUrl رابط العودة عند نجاح الدفع
     * @param string $cancelUrl رابط العودة عند إلغاء الدفع
     *
     * @throws ApiErrorException
     * @throws \InvalidArgumentException إذا الخطة ليس لها stripe_price_id
     */
    public function createCheckoutSession(
        Company $company,
        SubscriptionPlan $plan,
        SubscriptionUpgradeRequest $upgradeRequest,
        int $months,
        string $successUrl,
        string $cancelUrl
    ): Session {
        if (! $plan->hasStripePrice()) {
            throw new \InvalidArgumentException(
                "الخطة \"{$plan->name}\" غير مربوطة بـ Stripe. أضف stripe_price_id أولاً."
            );
        }

        $customer = $this->getOrCreateCustomer($company);

        return Session::create([
            'customer'    => $customer->id,
            'mode'        => 'payment', // one-time payment (مستقبلاً: 'subscription' للـ recurring)
            'line_items'  => [
                [
                    'price'    => $plan->stripe_price_id,
                    'quantity' => $months, // عدد الأشهر = الكمية
                ],
            ],
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => $cancelUrl,
            'metadata'    => [
                // نحفظ الـ IDs في metadata لاسترجاعها في الـ Webhook
                'upgrade_request_id' => $upgradeRequest->id,
                'company_id'         => $company->id,
                'plan_id'            => $plan->id,
                'months'             => $months,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'upgrade_request_id' => $upgradeRequest->id,
                    'company_id'         => $company->id,
                ],
            ],
            // إغلاق Session بعد 30 دقيقة
            'expires_at' => now()->addMinutes(30)->timestamp,
        ]);
    }

    /**
     * جلب Checkout Session من Stripe بالـ ID.
     *
     * @throws ApiErrorException
     */
    public function retrieveSession(string $sessionId): Session
    {
        return Session::retrieve([
            'id'     => $sessionId,
            'expand' => ['payment_intent', 'line_items'],
        ]);
    }

    // ========================================================
    // Webhook Verification
    // ========================================================

    /**
     * التحقق من صحة Stripe Webhook Signature.
     *
     * يجب استدعاؤها قبل معالجة أي webhook.
     * إذا فشل التحقق → الطلب مزوَّر → ترمي Exception.
     *
     * @param string $payload محتوى الـ request (raw body)
     * @param string $signature قيمة header: Stripe-Signature
     *
     * @throws \Stripe\Exception\SignatureVerificationException
     */
    public function constructWebhookEvent(string $payload, string $signature): \Stripe\Event
    {
        return Webhook::constructEvent(
            $payload,
            $signature,
            config('services.stripe.webhook_secret')
        );
    }

    // ========================================================
    // Helpers
    // ========================================================

    /**
     * تحويل cents لـ decimal.
     * Stripe يخزن المبالغ بـ cents: 2999 = $29.99
     */
    public function fromCents(int $cents): float
    {
        return $cents / 100;
    }

    /**
     * تحويل decimal لـ cents.
     */
    public function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }
}