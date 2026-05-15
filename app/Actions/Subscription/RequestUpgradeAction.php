<?php

namespace App\Actions\Subscription;

use App\Enums\UpgradeRequestStatus;
use App\Models\Company;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionUpgradeRequest;
use App\Notifications\SubscriptionRequested;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\DB;

class RequestUpgradeAction
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    /**
     * إنشاء طلب ترقية جديد.
     *
     * يتحقق من:
     *   1. لا يوجد طلب pending آخر للشركة
     *   2. الخطة المطلوبة مختلفة عن الحالية
     *   3. الخطة المطلوبة ليست مجانية
     *
     * يُنشئ:
     *   - سجل في subscription_upgrade_requests
     *   - إشعار لجميع admins
     *
     * @throws \RuntimeException إذا فشل أي شرط
     */
    public function execute(
        Company $company,
        SubscriptionPlan $toPlan,
        int $months = 1,
        ?string $notes = null
    ): SubscriptionUpgradeRequest {
        // ── Guard 1: لا طلب pending موجود ──────────────────────
        if (SubscriptionUpgradeRequest::hasPendingRequest($company->id)) {
            throw new \RuntimeException(
                'لديك طلب ترقية قيد المراجعة. يرجى الانتظار أو إلغاء الطلب الحالي أولاً.'
            );
        }

        // ── Guard 2: لا ترقية لنفس الخطة ──────────────────────
        $currentPlan = $this->subscriptionService->getCurrentPlan($company);
        if ($currentPlan->id === $toPlan->id) {
            throw new \RuntimeException('أنت مشترك في هذه الخطة بالفعل.');
        }

        // ── Guard 3: لا ترقية للخطة المجانية ──────────────────
        if ($toPlan->isFree()) {
            throw new \RuntimeException('لا يمكن طلب الترقية إلى الخطة المجانية.');
        }

        // ── إنشاء الطلب + إشعار الأدمن ─────────────────────────
        $request = DB::transaction(function () use ($company, $toPlan, $currentPlan, $months, $notes) {
            $upgradeRequest = SubscriptionUpgradeRequest::create([
                'company_id'   => $company->id,
                'from_plan_id' => $currentPlan->isFree() ? null : $currentPlan->id,
                'to_plan_id'   => $toPlan->id,
                'months'       => $months,
                'amount'       => $toPlan->price * $months,
                'status'       => UpgradeRequestStatus::Pending->value,
                'notes'        => $notes,
                // الطلب ينتهي بعد 7 أيام إذا لم يُعالَج
                'expires_at'   => now()->addDays(7),
            ]);

            return $upgradeRequest;
        });

        // إشعار الأدمن — خارج Transaction لأنه لا يؤثر على البيانات
        $this->notifyAdmins($request);

        return $request;
    }

    /**
     * إشعار جميع المستخدمين الذين role = admin.
     */
    private function notifyAdmins(SubscriptionUpgradeRequest $request): void
    {
        \App\Models\User::where('role', 'admin')
            ->get()
            ->each(fn($admin) => $admin->notify(new SubscriptionRequested($request)));
    }
}