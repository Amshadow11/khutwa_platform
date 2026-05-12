<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
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
     * صفحة الاشتراك — تعرض الخطة الحالية + مقارنة الخطط
     */
    public function index(): View
    {
        $company = Auth::guard('company')->user();

        $currentPlan       = $this->subscriptionService->getCurrentPlan($company);
        $activeSubscription= $this->subscriptionService->getActiveSubscription($company);
        $usageSummary      = $this->subscriptionService->getUsageSummary($company);
        $plans             = SubscriptionPlan::with('features')
                                ->active()
                                ->public()
                                ->get();

        return view('company.subscription.index', compact(
            'company',
            'currentPlan',
            'activeSubscription',
            'usageSummary',
            'plans'
        ));
    }

    /**
     * POST /company/subscription/request
     * طلب ترقية — يُرسل إشعاراً للأدمن (بدون دفع مباشر الآن)
     */
    public function requestUpgrade(Request $request): RedirectResponse
    {
        $request->validate([
            'plan_id' => ['required', 'exists:subscription_plans,id'],
        ]);

        $company = Auth::guard('company')->user();
        $plan    = SubscriptionPlan::findOrFail($request->plan_id);

        // في المستقبل: إعادة توجيه لـ Payment Gateway
        // الآن: إشعار للأدمن بالطلب
        // TODO: NotifyAdminOfUpgradeRequest

        return back()->with('success',
            "تم إرسال طلبك للترقية إلى خطة \"{$plan->name}\". سيتواصل معك الفريق قريباً."
        );
    }
}