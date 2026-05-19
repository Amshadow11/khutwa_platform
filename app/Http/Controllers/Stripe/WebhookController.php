<?php

namespace App\Http\Controllers\Stripe;

use App\Actions\Stripe\HandleSuccessfulPaymentAction;
use App\Http\Controllers\Controller;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private readonly StripeService $stripeService,
        private readonly HandleSuccessfulPaymentAction $handlePaymentAction,
    ) {}

    /**
     * POST /stripe/webhook
     *
     * نقطة الاستقبال لجميع Stripe Events.
     *
     * ⚠️ يجب إعفاء هذا الـ route من CSRF verification.
     *    راجع: bootstrap/app.php → $middleware->validateCsrfTokens(except: [...])
     *
     * ⚠️ يجب قراءة raw body (php://input) وليس $request->all()
     *    لأن Stripe يتحقق من الـ signature على الـ raw payload.
     *
     * يجب أن يُرجع 200 دائماً عند استقبال الـ event —
     * حتى لو لم نعالجه — وإلا Stripe سيُعيد الإرسال.
     */
    public function handle(Request $request): Response
    {
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        // ── التحقق من صحة الـ Signature ─────────────────────────
        try {
            $event = $this->stripeService->constructWebhookEvent($payload, $signature);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe Webhook: signature verification failed', [
                'error' => $e->getMessage(),
            ]);
            return response('Invalid signature', 400);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe Webhook: invalid payload', [
                'error' => $e->getMessage(),
            ]);
            return response('Invalid payload', 400);
        }

        // ── معالجة الـ Events ────────────────────────────────────
        try {
            match ($event->type) {
                'checkout.session.completed'  => $this->handleCheckoutCompleted($event),
                'invoice.payment_succeeded'   => $this->handleInvoicePaymentSucceeded($event),
                'invoice.payment_failed'      => $this->handleInvoicePaymentFailed($event),
                'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event),
                default => Log::info("Stripe Webhook: event غير معالَج: {$event->type}"),
            };
        } catch (\Throwable $e) {
            // نُسجّل الخطأ لكن نُرجع 200 حتى لا يُعيد Stripe الإرسال بشكل متكرر
            Log::error('Stripe Webhook: خطأ في المعالجة', [
                'event'   => $event->type,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }

        // Stripe يتوقع 200 دائماً عند الاستقبال الناجح
        return response('OK', 200);
    }

    // ========================================================
    // Event Handlers
    // ========================================================

    /**
     * checkout.session.completed
     * → الشركة دفعت عبر Checkout → فعّل الاشتراك
     */
    private function handleCheckoutCompleted(\Stripe\Event $event): void
    {
        /** @var \Stripe\Checkout\Session $session */
        $session = $event->data->object;

        Log::info('Stripe Webhook: checkout.session.completed', [
            'session_id'         => $session->id,
            'payment_intent'     => $session->payment_intent,
            'amount_total'       => $session->amount_total,
            'upgrade_request_id' => $session->metadata->upgrade_request_id ?? null,
        ]);

        $this->handlePaymentAction->execute($session);
    }

    /**
     * invoice.payment_succeeded
     * → دفعة متكررة ناجحة (Recurring Subscriptions — مستقبلاً)
     *
     * حالياً: نُسجّل فقط
     */
    private function handleInvoicePaymentSucceeded(\Stripe\Event $event): void
    {
        /** @var \Stripe\Invoice $invoice */
        $invoice = $event->data->object;

        Log::info('Stripe Webhook: invoice.payment_succeeded', [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $invoice->customer,
            'amount_paid'    => $invoice->amount_paid,
        ]);

        // TODO: تجديد تلقائي للاشتراك عند تفعيل Recurring Subscriptions
    }

    /**
     * invoice.payment_failed
     * → فشل دفعة → إشعار الشركة
     *
     * حالياً: نُسجّل فقط
     */
    private function handleInvoicePaymentFailed(\Stripe\Event $event): void
    {
        /** @var \Stripe\Invoice $invoice */
        $invoice = $event->data->object;

        Log::warning('Stripe Webhook: invoice.payment_failed', [
            'invoice_id'  => $invoice->id,
            'customer_id' => $invoice->customer,
            'attempt'     => $invoice->attempt_count,
        ]);

        // TODO: إشعار الشركة بفشل الدفع + تعليق الاشتراك إذا تكرر
    }

    /**
     * customer.subscription.deleted
     * → Stripe ألغى الاشتراك (Recurring — مستقبلاً)
     *
     * حالياً: نُسجّل فقط
     */
    private function handleSubscriptionDeleted(\Stripe\Event $event): void
    {
        $subscription = $event->data->object;

        Log::info('Stripe Webhook: customer.subscription.deleted', [
            'stripe_subscription_id' => $subscription->id,
            'customer_id'            => $subscription->customer,
        ]);

        // TODO: إلغاء الاشتراك في قاعدة البيانات
    }
}