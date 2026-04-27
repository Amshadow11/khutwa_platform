@extends('layouts.company')

@section('title', 'لوحة التحكم')
@section('page-title', 'نظرة عامة')

@push('styles')
<style>
    /* ---- Stat Cards Row ---- */
    .stats-row { margin-bottom: 1.5rem; }

    /* ---- Recent Apps Table — Mobile Cards ---- */
    @media (max-width: 767px) {
        .desktop-table { display: none !important; }
        .mobile-cards  { display: block !important; }
    }
    @media (min-width: 768px) {
        .desktop-table { display: table !important; }
        .mobile-cards  { display: none !important; }
    }

    .app-card-mobile {
        background: #fff;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: .75rem;
        box-shadow: 0 1px 6px rgba(0,0,0,.05);
    }
    .applicant-avatar {
        width: 42px; height: 42px;
        border-radius: 50%;
        object-fit: cover;
        background: #e9ecef;
    }
</style>
@endpush

@section('content')

{{-- ===== Stat Cards ===== --}}
<div class="row g-3 stats-row">

    <div class="col-6 col-lg-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
            <div class="stat-value">{{ $stats['total_jobs'] }}</div>
            <div class="stat-label">إجمالي الوظائف</div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-value">{{ $stats['active_jobs'] }}</div>
            <div class="stat-label">الوظائف النشطة</div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card stat-orange">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value">{{ $stats['total_apps'] }}</div>
            <div class="stat-label">إجمالي الطلبات</div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card stat-purple">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-value">{{ $stats['pending_apps'] }}</div>
            <div class="stat-label">طلبات جديدة</div>
        </div>
    </div>

</div>

{{-- ===== Quick Actions (Mobile) ===== --}}
<div class="d-md-none mb-3">
    <a href="{{ route('company.jobs.create') }}"
       class="btn btn-primary w-100 rounded-pill py-2">
        <i class="fas fa-plus me-2"></i>نشر وظيفة جديدة
    </a>
</div>

{{-- ===== أحدث الطلبات ===== --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-clock me-2 text-primary"></i>أحدث الطلبات الواردة</span>
        <a href="{{ route('company.applications.index') }}"
           class="btn btn-sm btn-outline-primary rounded-pill px-3">
            عرض الكل
        </a>
    </div>

    @if($recentApplications->isEmpty())
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity:.3"></i>
            لا توجد طلبات بعد
            <div class="mt-3">
                <a href="{{ route('company.jobs.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">
                    انشر أول وظيفة
                </a>
            </div>
        </div>
    @else

        {{-- Desktop: Table --}}
        <div class="table-responsive">
            <table class="table desktop-table">
                <thead>
                    <tr>
                        <th>المتقدم</th>
                        <th>الوظيفة</th>
                        <th>تاريخ التقديم</th>
                        <th>الحالة</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentApplications as $app)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $app->user->avatar_url }}"
                                     class="applicant-avatar" alt="">
                                <div>
                                    <div class="fw-semibold">{{ $app->user->display_name }}</div>
                                    <div class="text-muted" style="font-size:.78rem">{{ $app->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $app->job->title ?? '—' }}</td>
                        <td class="text-muted" style="font-size:.82rem">
                            {{ $app->applied_at?->diffForHumans() }}
                        </td>
                        <td>
                            <span class="badge bg-{{ $app->status_color }}">
                                {{ $app->status_label }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('company.applications.show', $app) }}"
                               class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                عرض
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile: Cards --}}
        <div class="mobile-cards p-3">
            @foreach($recentApplications as $app)
            <div class="app-card-mobile">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="{{ $app->user->avatar_url }}"
                         class="applicant-avatar" alt="">
                    <div class="flex-grow-1">
                        <div class="fw-semibold" style="font-size:.9rem">{{ $app->user->display_name }}</div>
                        <div class="text-muted" style="font-size:.78rem">{{ $app->job->title ?? '—' }}</div>
                    </div>
                    <span class="badge bg-{{ $app->status_color }}">
                        {{ $app->status_label }}
                    </span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted" style="font-size:.75rem">
                        <i class="fas fa-clock me-1"></i>{{ $app->applied_at?->diffForHumans() }}
                    </span>
                    <a href="{{ route('company.applications.show', $app) }}"
                       class="btn btn-sm btn-outline-primary rounded-pill px-3" style="font-size:.78rem">
                        عرض التفاصيل
                    </a>
                </div>
            </div>
            @endforeach
        </div>

    @endif
</div>

{{-- ===== رابط سريع لنشر وظيفة (Desktop) ===== --}}
@if($stats['total_jobs'] === 0)
<div class="card mt-3">
    <div class="card-body text-center py-4">
        <i class="fas fa-rocket fa-2x text-primary mb-3 d-block"></i>
        <h5>ابدأ بنشر أول وظيفة</h5>
        <p class="text-muted small">انشر وظائفك واستقطب أفضل المواهب اليمنية</p>
        <a href="{{ route('company.jobs.create') }}"
           class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-plus me-2"></i>نشر وظيفة الآن
        </a>
    </div>
</div>
@endif

@endsection
