@extends('layouts.app')
@section('title', 'لوحة التحكم')

@push('styles')
<style>
    .user-content { padding: 1.5rem; }
    .stat-card-u {
        background:#fff; border-radius:12px; padding:1.25rem;
        box-shadow:0 2px 10px rgba(0,0,0,.05); text-align:center;
    }
    .stat-card-u .icon {
        width:48px;height:48px;border-radius:12px;
        display:flex;align-items:center;justify-content:center;
        font-size:1.2rem;margin:0 auto .75rem;
    }
    .stat-card-u .val  { font-size:1.6rem;font-weight:700;color:#333; }
    .stat-card-u .lbl  { font-size:.82rem;color:#888;margin-top:.2rem; }
    .app-item {
        display:flex;align-items:center;gap:.75rem;
        padding:.85rem 1.25rem;border-bottom:1px solid #f5f5f5;
        transition:background .15s;
    }
    .app-item:hover  { background:#fafafa; }
    .app-item:last-child { border-bottom:none; }
    .co-logo {
        width:42px;height:42px;border-radius:10px;
        object-fit:cover;background:#f0f0f0;flex-shrink:0;
    }
    @media(max-width:768px){ .user-content { padding:1rem; } }
</style>
@endpush

@section('content')
<div class="user-content">

    {{-- Greeting --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">مرحباً، {{ $user->display_name }} 👋</h4>
            <p class="text-muted small mb-0">إليك ملخص نشاطك على المنصة</p>
        </div>
        <a href="{{ route('jobs.index') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-search me-1"></i>ابحث عن وظيفة
        </a>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="stat-card-u">
                <div class="icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="val">{{ $stats['total_applications'] }}</div>
                <div class="lbl">طلباتي</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card-u">
                <div class="icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="val">{{ $stats['pending'] }}</div>
                <div class="lbl">قيد المراجعة</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card-u">
                <div class="icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="val">{{ $stats['accepted'] }}</div>
                <div class="lbl">مقبولة</div>
            </div>
        </div>
    </div>

    {{-- تحذير إكمال الملف --}}
    @if(!$user->profile_picture || !$user->bio || !$user->phone)
    <div class="alert alert-warning alert-dismissible fade show mb-4"
         style="border-radius:12px;border:none">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>أكمل ملفك الشخصي</strong> — الملف الكامل يزيد فرصك بشكل كبير
        <a href="{{ route('user.profile.show') }}" class="btn btn-sm btn-warning rounded-pill px-3 ms-2">تكملة الملف</a>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- آخر الطلبات --}}
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="fas fa-history me-2 text-primary"></i>آخر طلباتي</span>
            <a href="{{ route('user.applications.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">عرض الكل</a>
        </div>

        @if($recentApplications->isEmpty())
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-search fa-3x mb-3 d-block" style="opacity:.2"></i>
                لم تتقدم على أي وظيفة بعد
                <div class="mt-3">
                    <a href="#" class="btn btn-primary rounded-pill px-4">ابحث عن وظائف الآن</a>
                </div>
            </div>
        @else
            @foreach($recentApplications as $app)
            <div class="app-item">
                <img src="{{ $app->job->company->logo_url ?? asset('images/default-company.png') }}"
                     class="co-logo" alt="">
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-semibold"
                         style="font-size:.9rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        {{ $app->job->title ?? 'وظيفة' }}
                    </div>
                    <div class="text-muted" style="font-size:.78rem">
                        {{ $app->job->company->company_name ?? '—' }}
                        @if($app->job->location ?? null)
                            &ensp;<i class="fas fa-map-marker-alt me-1"></i>{{ $app->job->location }}
                        @endif
                    </div>
                </div>
                <div class="text-end flex-shrink-0">
                    <span class="badge bg-{{ $app->status_color }} mb-1 d-block">
                        {{ $app->status_label }}
                    </span>
                    <span class="text-muted" style="font-size:.72rem">
                        {{ $app->applied_at?->diffForHumans() }}
                    </span>
                </div>
            </div>
            @endforeach
        @endif
    </div>

</div>
@endsection
