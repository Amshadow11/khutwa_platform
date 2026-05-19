@extends('layouts.company')

@section('title', 'خطة الاشتراك')
@section('page-title', 'خطة الاشتراك')

@push('styles')
<style>
.plan-card {
    border: 2px solid #e5e7eb;
    border-radius: 16px;
    padding: 1.75rem;
    height: 100%;
    transition: all .25s;
    position: relative;
    background: #fff;
}
.plan-card:hover { border-color: #2C5AA0; box-shadow: 0 8px 30px rgba(44,90,160,.1); }
.plan-card.current {
    border-color: #2C5AA0;
    background: linear-gradient(135deg, #f0f5ff, #fff);
}
.plan-card.popular { border-color: #10b981; }
.plan-badge {
    position: absolute;
    top: -12px;
    right: 50%;
    transform: translateX(50%);
    background: #10b981;
    color: #fff;
    font-size: .75rem;
    font-weight: 700;
    padding: .25rem .85rem;
    border-radius: 20px;
}
.plan-name  { font-size: 1.3rem; font-weight: 700; color: #1e293b; }
.plan-price { font-size: 2.5rem; font-weight: 800; color: #2C5AA0; line-height: 1; }
.plan-price sup  { font-size: 1rem; font-weight: 600; }
.plan-price span { font-size: .9rem; font-weight: 400; color: #94a3b8; }
.plan-feature {
    display: flex;
    align-items: center;
    gap: .5rem;
    font-size: .88rem;
    color: #475569;
    padding: .3rem 0;
}
.plan-feature .check     { color: #10b981; }
.plan-feature .cross     { color: #ef4444; }
.plan-feature .unlimited { color: #2C5AA0; font-weight: 600; }

.usage-bar-wrap { margin-bottom: 1rem; }
.usage-label {
    font-size: .82rem;
    color: #64748b;
    margin-bottom: .25rem;
    display: flex;
    justify-content: space-between;
}
.usage-bar       { height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; }
.usage-bar-fill  { height: 100%; border-radius: 4px; transition: width .5s; }
.usage-ok        { background: #10b981; }
.usage-warning   { background: #f59e0b; }
.usage-danger    { background: #ef4444; }

.btn-upgrade {
    width: 100%;
    padding: .65rem;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: all .2s;
    margin-top: 1.25rem;
    font-size: .95rem;
}
.btn-upgrade.primary      { background: linear-gradient(135deg, #2C5AA0, #1e4085); color: #fff; }
.btn-upgrade.primary:hover { opacity: .92; transform: translateY(-1px); }
.btn-upgrade.outline      { background: transparent; color: #2C5AA0; border: 2px solid #2C5AA0; }
.btn-upgrade.current-btn  { background: #e5e7eb; color: #94a3b8; cursor: default; }
.btn-upgrade.pending-btn  { background: #fef3c7; color: #92400e; border: 2px solid #fcd34d; cursor: default; }

/* بطاقة الطلب المعلّق */
.pending-request-card {
    background: linear-gradient(135deg, #fffbeb, #fff);
    border: 2px solid #fcd34d;
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
}
</style>
@endpush

@section('content')

{{-- ===== رسائل النظام ===== --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" style="border-radius:12px;border:none">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" style="border-radius:12px;border:none">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ===== بطاقة الطلب المعلّق ===== --}}
{{-- تظهر فقط إذا كان هناك طلب pending --}}
@if($pendingRequest)
    <div class="pending-request-card mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div style="width:44px;height:44px;background:#fef3c7;border-radius:12px;display:flex;align-items:center;justify-content:center">
                    <i class="fas fa-clock" style="color:#f59e0b;font-size:1.2rem"></i>
                </div>
                <div>
                    <div style="font-weight:700;color:#92400e;font-size:.95rem">
                        طلب ترقية قيد المراجعة
                    </div>
                    <div style="font-size:.82rem;color:#78350f;margin-top:.15rem">
                        طلبت الترقية إلى خطة
                        <strong>{{ $pendingRequest->toPlan->name }}</strong>
                        بتاريخ {{ $pendingRequest->created_at->format('d/m/Y') }}
                        @if($pendingRequest->expires_at)
                            · ينتهي الطلب في {{ $pendingRequest->expires_at->format('d/m/Y') }}
                        @endif
                    </div>
                    @if($pendingRequest->isExpired())
                        <div class="mt-1">
                            <span class="badge bg-danger" style="font-size:.75rem">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                انتهت صلاحية الطلب — يرجى إرسال طلب جديد
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- زر إلغاء الطلب --}}
            @if($pendingRequest->canBeCancelledByCompany())
                <form action="{{ route('company.subscription.cancel', $pendingRequest->id) }}"
                      method="POST"
                      onsubmit="return confirm('هل أنت متأكد من إلغاء طلب الترقية؟')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            style="background:none;border:1.5px solid #ef4444;color:#ef4444;border-radius:8px;padding:.4rem .9rem;font-size:.82rem;font-weight:600;font-family:inherit;cursor:pointer;transition:all .2s"
                            onmouseover="this.style.background='#ef4444';this.style.color='#fff'"
                            onmouseout="this.style.background='none';this.style.color='#ef4444'">
                        <i class="fas fa-times me-1"></i>إلغاء الطلب
                    </button>
                </form>
            @endif
        </div>
    </div>
@endif

{{-- ===== الخطة الحالية + الاستهلاك ===== --}}
<div class="row g-4 mb-4">

    {{-- بطاقة الخطة الحالية --}}
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:48px;height:48px;background:linear-gradient(135deg,#2C5AA0,#1e4085);border-radius:12px;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-crown text-white"></i>
                    </div>
                    <div>
                        <div style="font-size:.8rem;color:#94a3b8">خطتك الحالية</div>
                        <div style="font-size:1.3rem;font-weight:700;color:#1e293b">{{ $currentPlan->name }}</div>
                    </div>
                </div>

                @if($activeSubscription)
                    <div class="mb-3">
                        @if($activeSubscription->isOnTrial())
                            <span class="badge bg-info">
                                تجربة مجانية — تنتهي {{ $activeSubscription->trial_ends_at->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="badge bg-success">اشتراك نشط</span>
                            @if($activeSubscription->ends_at)
                                <span class="text-muted ms-2" style="font-size:.82rem">
                                    ينتهي {{ $activeSubscription->ends_at->format('d/m/Y') }}
                                    ({{ $activeSubscription->daysRemaining() }} يوم متبقي)
                                </span>
                            @else
                                <span class="text-muted ms-2" style="font-size:.82rem">غير محدود</span>
                            @endif
                        @endif
                    </div>
                @else
                    <span class="badge bg-secondary mb-3">الخطة المجانية</span>
                @endif

                <hr>

                {{-- مميزات الخطة الحالية --}}
                @php
                    $features = [
                        'max_jobs_per_month' => ['label' => 'وظائف شهرياً',       'icon' => 'briefcase'],
                        'featured_jobs'      => ['label' => 'وظائف مميزة',        'icon' => 'star'],
                        'urgent_jobs'        => ['label' => 'وظائف عاجلة',        'icon' => 'bolt'],
                        'messaging_limit'    => ['label' => 'رسائل شهرياً',       'icon' => 'envelope'],
                        'analytics'          => ['label' => 'إحصائيات متقدمة',    'icon' => 'chart-bar'],
                        'ai_matching'        => ['label' => 'AI Matching',         'icon' => 'robot'],
                    ];
                @endphp

                @foreach($features as $key => $info)
                    @php $val = $currentPlan->getFeature($key, '0'); @endphp
                    <div class="plan-feature">
                        <i class="fas fa-{{ $info['icon'] }}" style="width:16px;color:#2C5AA0"></i>
                        <span>{{ $info['label'] }}:</span>
                        @if($val === '-1')
                            <span class="unlimited ms-auto">غير محدود</span>
                        @elseif(in_array($val, ['true', '1']))
                            <i class="fas fa-check check ms-auto"></i>
                        @elseif(in_array($val, ['false', '0']))
                            <i class="fas fa-times cross ms-auto"></i>
                        @else
                            <strong class="ms-auto">{{ $val }}</strong>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- الاستهلاك الشهري --}}
    <div class="col-md-7">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#1e293b">
                    <i class="fas fa-chart-pie me-2" style="color:#2C5AA0"></i>
                    استهلاكك هذا الشهر — {{ now()->translatedFormat('F Y') }}
                </h6>

                {{-- وظائف --}}
                @php
                    $jobUsed  = $usageSummary['jobs']['used'];
                    $jobLimit = $usageSummary['jobs']['limit'];
                    $jobPct   = $usageSummary['jobs']['unlimited']
                                    ? 10
                                    : ($jobLimit > 0 ? min(100, round($jobUsed / $jobLimit * 100)) : 0);
                    $jobClass = $jobPct >= 90 ? 'usage-danger' : ($jobPct >= 70 ? 'usage-warning' : 'usage-ok');
                @endphp
                <div class="usage-bar-wrap">
                    <div class="usage-label">
                        <span><i class="fas fa-briefcase me-1"></i>الوظائف المنشورة</span>
                        <span>{{ $jobUsed }} / {{ $usageSummary['jobs']['unlimited'] ? '∞' : $jobLimit }}</span>
                    </div>
                    <div class="usage-bar">
                        <div class="usage-bar-fill {{ $jobClass }}"
                             style="width:{{ $usageSummary['jobs']['unlimited'] ? 10 : $jobPct }}%"></div>
                    </div>
                </div>

                {{-- رسائل --}}
                @php
                    $msgUsed  = $usageSummary['messages']['used'];
                    $msgLimit = $usageSummary['messages']['limit'];
                    $msgPct   = $usageSummary['messages']['unlimited']
                                    ? 10
                                    : ($msgLimit > 0 ? min(100, round($msgUsed / $msgLimit * 100)) : 0);
                    $msgClass = $msgPct >= 90 ? 'usage-danger' : ($msgPct >= 70 ? 'usage-warning' : 'usage-ok');
                @endphp
                <div class="usage-bar-wrap">
                    <div class="usage-label">
                        <span><i class="fas fa-envelope me-1"></i>الرسائل المُرسَلة</span>
                        <span>{{ $msgUsed }} / {{ $usageSummary['messages']['unlimited'] ? '∞' : $msgLimit }}</span>
                    </div>
                    <div class="usage-bar">
                        <div class="usage-bar-fill {{ $msgClass }}"
                             style="width:{{ $usageSummary['messages']['unlimited'] ? 10 : $msgPct }}%"></div>
                    </div>
                </div>

                {{-- وظائف مميزة --}}
                @php
                    $featUsed  = $usageSummary['featured']['used'];
                    $featLimit = $usageSummary['featured']['limit'];
                    $featPct   = $usageSummary['featured']['unlimited']
                                    ? 10
                                    : ($featLimit > 0 ? min(100, round($featUsed / $featLimit * 100)) : 0);
                @endphp
                <div class="usage-bar-wrap">
                    <div class="usage-label">
                        <span><i class="fas fa-star me-1"></i>الوظائف المميزة</span>
                        <span>
                            {{ $featUsed }} /
                            {{ $usageSummary['featured']['unlimited'] ? '∞' : ($featLimit === 0 ? 'غير متاح' : $featLimit) }}
                        </span>
                    </div>
                    <div class="usage-bar">
                        <div class="usage-bar-fill usage-ok"
                             style="width:{{ $featLimit > 0 ? $featPct : 0 }}%"></div>
                    </div>
                </div>

                @if($usageSummary['jobs']['remaining'] <= 1 && ! $usageSummary['jobs']['unlimited'])
                    <div class="alert alert-warning mt-3 py-2" style="border-radius:10px;font-size:.85rem">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        تبقّى لك <strong>{{ $usageSummary['jobs']['remaining'] }}</strong> وظيفة فقط هذا الشهر.
                        <a href="#plans">قم بالترقية</a> للحصول على المزيد.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ===== مقارنة الخطط ===== --}}
<div id="plans">
    <h5 class="fw-bold mb-4" style="color:#1e293b">
        <i class="fas fa-layer-group me-2" style="color:#2C5AA0"></i>
        الخطط المتاحة
    </h5>

    <div class="row g-4">
        @foreach($plans as $plan)
            @php $isCurrent = $plan->id === $currentPlan->id; @endphp
            <div class="col-md-6 col-lg-3">
                <div class="plan-card {{ $isCurrent ? 'current' : '' }} {{ $plan->slug === 'pro' ? 'popular' : '' }}">

                    @if($plan->slug === 'pro' && ! $isCurrent)
                        <div class="plan-badge">الأكثر شيوعاً</div>
                    @endif

                    @if($isCurrent)
                        <div class="plan-badge" style="background:#2C5AA0">خطتك الحالية</div>
                    @endif

                    <div class="plan-name mb-2">{{ $plan->name }}</div>
                    <div class="plan-price mb-3">
                        @if($plan->price == 0)
                            <span style="font-size:1.5rem;font-weight:700">مجاني</span>
                        @else
                            <sup>$</sup>{{ number_format($plan->price, 0) }}<span>/شهر</span>
                        @endif
                    </div>

                    @if($plan->description)
                        <p style="font-size:.82rem;color:#64748b;margin-bottom:1rem">{{ $plan->description }}</p>
                    @endif

                    <hr style="margin:.75rem 0">

                    {{-- مقارنة المميزات --}}
                    @foreach($features as $key => $info)
                        @php $val = $plan->getFeature($key, '0'); @endphp
                        <div class="plan-feature">
                            <i class="fas fa-{{ $info['icon'] }}"
                               style="width:16px;color:#2C5AA0;font-size:.8rem"></i>
                            <span>{{ $info['label'] }}</span>
                            @if($val === '-1')
                                <span class="unlimited ms-auto">∞</span>
                            @elseif(in_array($val, ['true', '1']))
                                <i class="fas fa-check check ms-auto"></i>
                            @elseif(in_array($val, ['false', '0']))
                                <i class="fas fa-times cross ms-auto"></i>
                            @else
                                <strong class="ms-auto">{{ $val }}</strong>
                            @endif
                        </div>
                    @endforeach

                    {{-- زر الترقية --}}
                    @if($isCurrent)
                        <button class="btn-upgrade current-btn" disabled>خطتك الحالية</button>

                    @elseif($pendingRequest && $pendingRequest->to_plan_id === $plan->id)
                        {{-- طلب معلّق لهذه الخطة --}}
                        <button class="btn-upgrade pending-btn" disabled>
                            <i class="fas fa-clock me-1"></i>طلب قيد المراجعة
                        </button>

                    @elseif($pendingRequest)
                        {{-- طلب معلّق لخطة أخرى — تعطيل الأزرار --}}
                        <button class="btn-upgrade current-btn" disabled
                                title="لديك طلب ترقية قيد المراجعة">
                            <i class="fas fa-lock me-1"></i>طلب معلّق
                        </button>

                    @elseif($plan->price == 0)
                        {{-- لا زر للخطة المجانية --}}

                    @else
                        @if($plan->hasStripePrice())
                            {{-- ✅ زر الدفع عبر Stripe (الخيار الرئيسي) --}}
                            <form action="{{ route('stripe.checkout.create') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button type="submit"
                                        class="btn-upgrade {{ $plan->slug === 'pro' ? 'primary' : 'outline' }}">
                                    <i class="fab fa-stripe me-1"></i>
                                    ادفع الآن — {{ $plan->name }}
                                </button>
                            </form>

                            {{-- رابط الطلب اليدوي كخيار بديل --}}
                            <form action="{{ route('company.subscription.request') }}" method="POST" class="mt-1">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button type="submit"
                                        style="width:100%;background:none;border:none;color:#94a3b8;font-size:.75rem;font-family:inherit;cursor:pointer;padding:.25rem;text-decoration:underline"
                                        onmouseover="this.style.color='#2C5AA0'"
                                        onmouseout="this.style.color='#94a3b8'">
                                    أو اطلب الترقية يدوياً
                                </button>
                            </form>

                        @else
                            {{-- ✅ طلب يدوي فقط (الخطة بدون Stripe Price) --}}
                            <form action="{{ route('company.subscription.request') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button type="submit"
                                        class="btn-upgrade {{ $plan->slug === 'pro' ? 'primary' : 'outline' }}">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    الترقية إلى {{ $plan->name }}
                                </button>
                            </form>
                        @endif
                    @endif

                    @if($plan->trial_days > 0 && ! $isCurrent && ! $pendingRequest)
                        @if(! $company->hasUsedTrial() && ! $activeSubscription)
                            {{-- زر التجربة المجانية --}}
                            <form action="{{ route('company.subscription.trial') }}" method="POST" class="mt-2">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button type="submit"
                                        style="width:100%;padding:.5rem;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:700;font-family:inherit;cursor:pointer;transition:all .2s"
                                        onmouseover="this.style.opacity='.9'"
                                        onmouseout="this.style.opacity='1'">
                                    <i class="fas fa-gift me-1"></i>
                                    ابدأ التجربة المجانية — {{ $plan->trial_days }} يوم
                                </button>
                            </form>
                        @elseif($company->hasUsedTrial())
                            <div style="text-align:center;font-size:.75rem;color:#94a3b8;margin-top:.5rem">
                                <i class="fas fa-info-circle me-1"></i>
                                استخدمت التجربة المجانية
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

@endsection