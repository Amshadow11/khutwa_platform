<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionUpgradeRequest;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    /**
     * GET /company/subscription
     */
    public function index(): View
    {
        $company = Auth::guard('company')->user();

        $currentPlan        = $this->subscriptionService->getCurrentPlan($company);
        $activeSubscription = $this->subscriptionService->getActiveSubscription($company);
        $usageSummary       = $this->subscriptionService->getUsageSummary($company);
        $pendingRequest     = $this->subscriptionService->getPendingRequest($company);
        $plans              = SubscriptionPlan::with('features')
                                ->active()
                                ->public()
                                ->get();

        return view('company.subscription.index', compact(
            'company',
            'currentPlan',
            'activeSubscription',
            'usageSummary',
            'pendingRequest',
            'plans'
        ));
    }

    /**
     * POST /company/subscription/request
     * طلب ترقية حقيقي — ينشئ سجل + يُشعر الأدمن
     */
    public function requestUpgrade(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:subscription_plans,id'],
            'months'  => ['sometimes', 'integer', 'min:1', 'max:12'],
            'notes'   => ['nullable', 'string', 'max:500'],
        ]);

        $company = Auth::guard('company')->user();
        $plan    = SubscriptionPlan::findOrFail($validated['plan_id']);

        try {
            $this->subscriptionService->requestUpgrade(
                $company,
                $plan,
                $validated['months'] ?? 1,
                $validated['notes'] ?? null
            );

            return back()->with('success',
                "تم إرسال طلب الترقية إلى خطة \"{$plan->name}\" بنجاح. سيراجعه فريقنا خلال 24 ساعة."
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * DELETE /company/subscription/request/{request}
     * إلغاء طلب الترقية من الشركة
     */
    public function cancelRequest(SubscriptionUpgradeRequest $upgradeRequest): RedirectResponse
    {
        $company = Auth::guard('company')->user();

        try {
            $this->subscriptionService->cancelUpgradeRequest($company, $upgradeRequest);

            return back()->with('success', 'تم إلغاء طلب الترقية بنجاح.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
        /**
     * POST /company/subscription/trial
     * تفعيل التجربة المجانية — مرة واحدة فقط لكل شركة
     */
    public function startTrial(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:subscription_plans,id'],
        ]);

        $company = Auth::guard('company')->user();
        $plan    = SubscriptionPlan::findOrFail($validated['plan_id']);

        try {
            $this->subscriptionService->startFreeTrial($company, $plan);

            return back()->with('success',
                "🎉 تم تفعيل تجربتك المجانية لخطة \"{$plan->name}\" لمدة {$plan->trial_days} يوم!"
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}