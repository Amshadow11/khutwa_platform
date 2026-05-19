<?php

namespace App\Http\Controllers\Stripe;

use App\Actions\Stripe\CreateCheckoutSessionAction;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionUpgradeRequest;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CreateCheckoutSessionAction $createCheckoutAction,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    // ========================================================
    // بدء الـ Checkout
    // ========================================================

    /**
     * POST /stripe/checkout
     *
     * ينشئ Stripe Checkout Session ويوجّه الشركة للدفع.
     */
    public function create(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:subscription_plans,id'],
            'months'  => ['sometimes', 'integer', 'min:1', 'max:12'],
            'notes'   => ['nullable', 'string', 'max:500'],
        ]);

        $company = Auth::guard('company')->user();
        $plan    = SubscriptionPlan::findOrFail($validated['plan_id']);

        try {
            $result = $this->createCheckoutAction->execute(
                company: $company,
                plan:    $plan,
                months:  $validated['months'] ?? 1,
                notes:   $validated['notes'] ?? null,
            );

            // توجيه الشركة لـ Stripe Checkout
            return redirect()->away($result['checkout_url']);

        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('company.subscription.index')
                ->with('error', $e->getMessage());

        } catch (\RuntimeException $e) {
            return redirect()
                ->route('company.subscription.index')
                ->with('error', $e->getMessage());

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return redirect()
                ->route('company.subscription.index')
                ->with('error', 'حدث خطأ في الاتصال بنظام الدفع. حاول مجدداً.');
        }
    }

    // ========================================================
    // صفحة النجاح
    // ========================================================

    /**
     * GET /stripe/checkout/success?session_id=cs_live_xxx
     *
     * Stripe يُوجّه هنا بعد نجاح الدفع.
     * الاشتراك يُفعَّل من الـ Webhook — هنا فقط نعرض رسالة.
     *
     * ⚠️ لا تُفعّل الاشتراك هنا — الـ Webhook قد يصل قبل أو بعد هذا الـ redirect.
     *    الـ Webhook هو المصدر الموثوق لتفعيل الاشتراك.
     */
    public function success(Request $request): RedirectResponse
    {
        $sessionId = $request->query('session_id');

        if ($sessionId) {
            // نتحقق أن الطلب موجود لعرض رسالة دقيقة
            $upgradeRequest = SubscriptionUpgradeRequest::where('stripe_session_id', $sessionId)
                ->first();

            if ($upgradeRequest && $upgradeRequest->isApproved()) {
                return redirect()
                    ->route('company.subscription.index')
                    ->with('success', '🎉 تم الدفع وتفعيل اشتراكك بنجاح!');
            }
        }

        // الـ Webhook قد لم يصل بعد — نعرض رسالة انتظار
        return redirect()
            ->route('company.subscription.index')
            ->with('info', 'تم استلام دفعتك بنجاح. سيتم تفعيل اشتراكك خلال لحظات.');
    }

    // ========================================================
    // صفحة الإلغاء
    // ========================================================

    /**
     * GET /stripe/checkout/cancel
     *
     * الشركة ضغطت "Back" في Stripe Checkout.
     * الطلب لا يزال pending — يمكن للشركة إلغاؤه.
     */
    public function cancel(): RedirectResponse
    {
        return redirect()
            ->route('company.subscription.index')
            ->with('info', 'تم إلغاء عملية الدفع. طلب الترقية لا يزال نشطاً — يمكنك المتابعة لاحقاً أو إلغاء الطلب.');
    }
}