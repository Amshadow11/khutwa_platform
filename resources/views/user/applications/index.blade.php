@extends('layouts.app')
@section('title', 'طلباتي')

@push('styles')
<style>
    .page-wrap { padding: 1.5rem 0 3rem; }

    /* Stats */
    .stat-u {
        background:#fff; border-radius:12px; padding:1rem;
        box-shadow:0 2px 10px rgba(0,0,0,.05); text-align:center;
    }
    .stat-u .val { font-size:1.5rem; font-weight:800; line-height:1; }
    .stat-u .lbl { font-size:.75rem; color:#999; margin-top:.25rem; }

    /* Application Card */
    .app-card {
        border:none; border-radius:12px;
        box-shadow:0 2px 10px rgba(0,0,0,.05);
        margin-bottom:.75rem; transition:box-shadow .2s;
    }
    .app-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.09); }

    .company-logo {
        width:50px; height:50px; border-radius:10px;
        object-fit:cover; background:#f4f6fb;
        border:1px solid #eee; flex-shrink:0;
    }

    /* Status Timeline (Desktop) */
    .status-steps {
        display:flex; align-items:center; gap:0;
        margin-top:.75rem;
    }
    .status-step {
        display:flex; flex-direction:column; align-items:center;
        flex:1; position:relative;
    }
    .status-step::before {
        content:''; position:absolute; top:10px; right:50%;
        width:100%; height:2px; background:#e9ecef; z-index:0;
    }
    .status-step:first-child::before { display:none; }
    .status-step .dot {
        width:20px; height:20px; border-radius:50%;
        background:#e9ecef; border:3px solid #fff;
        box-shadow:0 0 0 2px #e9ecef;
        z-index:1; position:relative;
    }
    .status-step.done .dot      { background:#2C5AA0; box-shadow:0 0 0 2px #2C5AA0; }
    .status-step.done::before   { background:#2C5AA0; }
    .status-step.current .dot   { background:#F8B500; box-shadow:0 0 0 2px #F8B500; }
    .status-step.rejected .dot  { background:#dc3545; box-shadow:0 0 0 2px #dc3545; }
    .status-step .step-label    { font-size:.62rem; color:#aaa; margin-top:.35rem; text-align:center; }
    .status-step.done .step-label    { color:#2C5AA0; font-weight:600; }
    .status-step.current .step-label { color:#333; font-weight:700; }

    @media(max-width:575px) { .status-steps { display:none; } }
</style>
@endpush

@section('content')
<div class="page-wrap">
<div class="container">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">طلباتي</h4>
            <small class="text-muted">تتبع حالة جميع طلبات التقديم</small>
        </div>
        <a href="{{ route('jobs.index') }}" class="btn btn-primary rounded-pill px-4 btn-sm">
            <i class="fas fa-search me-1"></i>ابحث عن وظائف
        </a>
    </div>

    {{-- Stats --}}
    <div class="row g-2 mb-4">
        @foreach([
            ['val'=>$stats['total'],       'lbl'=>'إجمالي', 'color'=>'#2C5AA0'],
            ['val'=>$stats['pending'],     'lbl'=>'قيد المراجعة','color'=>'#F8B500'],
            ['val'=>$stats['shortlisted'], 'lbl'=>'مختصرة',  'color'=>'#6f42c1'],
            ['val'=>$stats['accepted'],    'lbl'=>'مقبولة',  'color'=>'#28a745'],
            ['val'=>$stats['rejected'],    'lbl'=>'مرفوضة', 'color'=>'#dc3545'],
        ] as $s)
        <div class="col">
            <div class="stat-u">
                <div class="val" style="color:{{ $s['color'] }}">{{ $s['val'] }}</div>
                <div class="lbl">{{ $s['lbl'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- فلتر الحالة --}}
    <div class="d-flex gap-2 mb-3 flex-wrap">
        <a href="{{ route('user.applications.index') }}"
           class="btn btn-sm rounded-pill px-3 {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }}">
            الكل
        </a>
        @foreach(['pending'=>'قيد المراجعة','viewed'=>'تمت المشاهدة','shortlisted'=>'مختصرة','interview'=>'مقابلة','accepted'=>'مقبولة','rejected'=>'مرفوضة'] as $v=>$l)
        <a href="{{ request()->fullUrlWithQuery(['status'=>$v]) }}"
           class="btn btn-sm rounded-pill px-3 {{ request('status')===$v ? 'btn-primary' : 'btn-outline-secondary' }}">
            {{ $l }}
            @if($stats[$v] ?? 0) <span class="ms-1 opacity-75">({{ $stats[$v] }})</span> @endif
        </a>
        @endforeach
    </div>

    @if($applications->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-paper-plane fa-3x mb-3 d-block" style="opacity:.2"></i>
                لا توجد طلبات تقديم بعد
                <div class="mt-3">
                    <a href="{{ route('jobs.index') }}" class="btn btn-primary rounded-pill px-4">
                        ابحث عن وظيفة الآن
                    </a>
                </div>
            </div>
        </div>
    @else

    @foreach($applications as $app)
    <div class="app-card card">
        <div class="card-body p-3">

            {{-- Header --}}
            <div class="d-flex gap-3 align-items-start">
                <img src="{{ $app->job->company->logo_url ?? asset('images/default-company.png') }}"
                     class="company-logo" alt="">

                <div class="flex-grow-1 overflow-hidden">
                    {{-- Title + Status --}}
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                        <h6 class="fw-bold mb-0 text-truncate">
                            <a href="{{ route('user.applications.show', $app) }}"
                               class="text-dark text-decoration-none">
                                {{ $app->job->title ?? 'وظيفة' }}
                            </a>
                        </h6>
                        <span class="badge bg-{{ $app->status_color }} flex-shrink-0">
                            {{ $app->status_label }}
                        </span>
                    </div>

                    {{-- Company + Location --}}
                    <div class="text-primary small mb-1">
                        <i class="fas fa-building me-1"></i>
                        {{ $app->job->company->company_name ?? '—' }}
                        @if($app->job->location ?? null)
                            <span class="text-muted ms-2">
                                <i class="fas fa-map-marker-alt me-1"></i>{{ $app->job->location }}
                            </span>
                        @endif
                    </div>

                    {{-- Meta --}}
                    <div class="d-flex align-items-center gap-3 mb-2 text-muted" style="font-size:.75rem">
                        <span>
                            <i class="fas fa-clock me-1"></i>تقدّمت {{ $app->applied_at?->diffForHumans() }}
                        </span>
                        @if($app->job->job_type ?? null)
                        <span>
                            <i class="fas fa-briefcase me-1"></i>{{ $app->job->job_type_label ?? '' }}
                        </span>
                        @endif
                    </div>

                    {{-- Timeline Progress (Desktop) --}}
                    @php
                        $steps = ['pending','viewed','shortlisted','interview','accepted'];
                        $currentIdx = array_search($app->status, $steps);
                        $isRejected = $app->status === 'rejected';
                        $stepLabels = ['قيد المراجعة','تمت المشاهدة','قائمة مختصرة','مقابلة','مقبول'];
                    @endphp

                    @if(!$isRejected)
                    <div class="status-steps">
                        @foreach($steps as $i => $step)
                        <div class="status-step
                            {{ $i < $currentIdx ? 'done' : '' }}
                            {{ $i === $currentIdx ? 'current' : '' }}">
                            <div class="dot">
                                @if($i < $currentIdx)
                                    <i class="fas fa-check" style="font-size:.45rem;color:#fff;display:flex;align-items:center;justify-content:center;height:100%"></i>
                                @endif
                            </div>
                            <div class="step-label">{{ $stepLabels[$i] }}</div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="d-flex align-items-center gap-2 mt-1" style="font-size:.8rem;color:#dc3545">
                        <i class="fas fa-times-circle"></i>
                        <span>تم رفض الطلب</span>
                    </div>
                    @endif

                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex gap-2 justify-content-end mt-2 border-top pt-2">
                <a href="{{ route('user.applications.show', $app) }}"
                   class="btn btn-sm btn-outline-primary rounded-pill px-3" style="font-size:.78rem">
                    <i class="fas fa-eye me-1"></i>التفاصيل
                </a>
                @if(in_array($app->status, ['pending','viewed']))
                <form action="{{ route('user.applications.destroy', $app) }}"
                      method="POST"
                      onsubmit="return confirm('هل تريد سحب هذا الطلب؟')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3" style="font-size:.78rem">
                        <i class="fas fa-undo me-1"></i>سحب
                    </button>
                </form>
                @endif
            </div>

        </div>
    </div>
    @endforeach

    <div class="d-flex justify-content-center mt-3">
        {{ $applications->appends(request()->query())->links() }}
    </div>

    @endif
</div>
</div>
@endsection
