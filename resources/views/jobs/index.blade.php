@extends('layouts.app')

@section('title', 'الوظائف')
@section('description', 'تصفح آلاف الوظائف المتاحة في اليمن')

@push('styles')
<style>
    .jobs-page { padding: 1.5rem 0 3rem; }

    /* ===== Filter Sidebar ===== */
    .filter-panel {
        background: #fff; border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        padding: 1.25rem; position: sticky; top: 80px;
    }
    .filter-title { font-weight: 700; font-size: .82rem; color: #888;
                    text-transform: uppercase; letter-spacing:.05em;
                    margin-bottom: .75rem; }
    .filter-group { margin-bottom: 1.25rem; }
    .filter-chip {
        display: inline-block; padding: .35rem .85rem;
        border-radius: 20px; border: 1.5px solid #e5e7eb;
        font-size: .8rem; cursor: pointer; margin: .2rem .2rem .2rem 0;
        transition: all .2s; text-decoration: none; color: #555;
    }
    .filter-chip:hover, .filter-chip.active {
        background: #2C5AA0; border-color: #2C5AA0; color: #fff;
    }

    /* ===== Job Card ===== */
    .job-card {
        border: none; border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,.05);
        transition: transform .2s, box-shadow .2s;
        margin-bottom: .75rem;
    }
    .job-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,.09);
    }
    .job-logo {
        width: 52px; height: 52px; border-radius: 10px;
        object-fit: cover; background: #f4f6fb;
        border: 1px solid #eee; flex-shrink: 0;
    }
    .job-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .72rem; font-weight: 600; padding: .25rem .65rem;
        border-radius: 20px; background: rgba(44,90,160,.07); color: #2C5AA0;
    }
    .job-badge.remote  { background: rgba(40,167,69,.07);  color: #28a745; }
    .job-badge.urgent  { background: rgba(220,53,69,.07);  color: #dc3545; }
    .job-badge.neutral { background: #f4f6fb; color: #666; }

    /* ===== Mobile Filter Offcanvas ===== */
    @media (max-width: 991px) {
        .filter-panel { position: static; margin-bottom: 1rem; display: none; }
    }
</style>
@endpush

@section('content')
<div class="jobs-page">
    <div class="container">

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-0">الوظائف المتاحة</h4>
                <small class="text-muted">{{ $jobs->total() }} وظيفة متاحة</small>
            </div>

            {{-- زر الفلتر في الموبايل --}}
            <button class="btn btn-outline-primary rounded-pill btn-sm d-lg-none"
                    data-bs-toggle="offcanvas" data-bs-target="#mobileFilters">
                <i class="fas fa-sliders-h me-1"></i>
                فلترة
                @if(array_filter($filters))
                    <span class="badge bg-primary ms-1">{{ count(array_filter($filters)) }}</span>
                @endif
            </button>
        </div>

        <div class="row g-3">

            {{-- ===== Desktop Filter Sidebar ===== --}}
            <div class="col-lg-3 d-none d-lg-block">
                <div class="filter-panel">

                    {{-- بحث نصي --}}
                    <div class="filter-group">
                        <div class="filter-title">البحث</div>
                        <form action="{{ route('jobs.index') }}" method="GET" id="filterForm">
                            @foreach(array_filter($filters, fn($v,$k) => $k !== 'keyword', ARRAY_FILTER_USE_BOTH) as $k => $v)
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                            @endforeach
                            <div class="input-group input-group-sm">
                                <input type="text" name="keyword" class="form-control"
                                       placeholder="مسمى الوظيفة..."
                                       value="{{ $filters['keyword'] ?? '' }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- المدينة --}}
                    <div class="filter-group">
                        <div class="filter-title">المدينة</div>
                        @foreach($cities as $city)
                            <a href="{{ request()->fullUrlWithQuery(['location' => ($filters['location'] ?? '') === $city ? null : $city]) }}"
                               class="filter-chip {{ ($filters['location'] ?? '') === $city ? 'active' : '' }}">
                                {{ $city }}
                            </a>
                        @endforeach
                    </div>

                    {{-- نوع الدوام --}}
                    <div class="filter-group">
                        <div class="filter-title">نوع الدوام</div>
                        @foreach(['full_time'=>'دوام كامل','part_time'=>'دوام جزئي','remote'=>'عن بُعد','contract'=>'عقد','freelance'=>'عمل حر'] as $v=>$l)
                            <a href="{{ request()->fullUrlWithQuery(['job_type' => ($filters['job_type'] ?? '') === $v ? null : $v]) }}"
                               class="filter-chip {{ ($filters['job_type'] ?? '') === $v ? 'active' : '' }}">
                                {{ $l }}
                            </a>
                        @endforeach
                    </div>

                    {{-- فلاتر إضافية --}}
                    <div class="filter-group">
                        <div class="filter-title">خيارات أخرى</div>
                        <a href="{{ request()->fullUrlWithQuery(['remote_work' => isset($filters['remote_work']) ? null : '1']) }}"
                           class="filter-chip {{ isset($filters['remote_work']) ? 'active' : '' }}">
                            <i class="fas fa-wifi me-1"></i>عن بُعد فقط
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['urgent' => isset($filters['urgent']) ? null : '1']) }}"
                           class="filter-chip {{ isset($filters['urgent']) ? 'active' : '' }}">
                            <i class="fas fa-bolt me-1"></i>عاجلة فقط
                        </a>
                    </div>

                    @if(array_filter($filters))
                        <a href="{{ route('jobs.index') }}"
                           class="btn btn-outline-secondary btn-sm w-100 rounded-pill">
                            <i class="fas fa-times me-1"></i>مسح كل الفلاتر
                        </a>
                    @endif

                </div>
            </div>

            {{-- ===== قائمة الوظائف ===== --}}
            <div class="col-lg-9">

                @if($jobs->isEmpty())
                    <div class="card">
                        <div class="card-body text-center py-5 text-muted">
                            <i class="fas fa-search fa-3x mb-3 d-block" style="opacity:.2"></i>
                            لا توجد وظائف تطابق بحثك
                            <div class="mt-3">
                                <a href="{{ route('jobs.index') }}"
                                   class="btn btn-outline-primary rounded-pill px-4">عرض كل الوظائف</a>
                            </div>
                        </div>
                    </div>
                @else

                    @foreach($jobs as $job)
                    <div class="card job-card">
                        <div class="card-body p-3">
                            <div class="d-flex gap-3">
                                <img src="{{ $job->company->logo_url }}"
                                     class="job-logo" alt="{{ $job->company->company_name }}">

                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                        <h6 class="fw-bold mb-0 text-truncate">
                                            <a href="{{ route('jobs.show', $job) }}"
                                               class="text-dark text-decoration-none">
                                                {{ $job->title }}
                                            </a>
                                        </h6>
                                        @if($job->urgent)
                                            <span class="badge bg-danger flex-shrink-0">⚡ عاجلة</span>
                                        @endif
                                    </div>

                                    <div class="text-primary small mb-2">
                                        <i class="fas fa-building me-1"></i>{{ $job->company->company_name }}
                                    </div>

                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                        <span class="job-badge">
                                            <i class="fas fa-clock"></i>{{ $job->job_type_label }}
                                        </span>
                                        @if($job->location)
                                            <span class="job-badge neutral">
                                                <i class="fas fa-map-marker-alt"></i>{{ $job->location }}
                                            </span>
                                        @endif
                                        @if($job->remote_work)
                                            <span class="job-badge remote">
                                                <i class="fas fa-wifi"></i>عن بُعد
                                            </span>
                                        @endif
                                        @if($job->experience_level)
                                            <span class="job-badge neutral">
                                                {{ match($job->experience_level) {
                                                    'junior'  => 'مبتدئ',
                                                    'mid'     => 'متوسط',
                                                    'senior'  => 'خبير',
                                                    'manager' => 'مدير',
                                                    default   => $job->experience_level
                                                } }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <div class="d-flex gap-3 align-items-center">
                                            @if($job->salary)
                                                <span class="text-success small fw-semibold">
                                                    <i class="fas fa-money-bill-wave me-1"></i>{{ $job->salary }}
                                                </span>
                                            @endif
                                            @if($job->deadline)
                                                <span class="text-muted" style="font-size:.75rem">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    ينتهي {{ $job->deadline->format('Y/m/d') }}
                                                    @if($job->days_remaining !== null && $job->days_remaining <= 3)
                                                        <span class="text-danger">({{ $job->days_remaining }} يوم)</span>
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-muted" style="font-size:.72rem">
                                                {{ $job->created_at->diffForHumans() }}
                                            </span>
                                            <a href="{{ route('jobs.show', $job) }}"
                                               class="btn btn-sm btn-primary rounded-pill px-3"
                                               style="font-size:.78rem">
                                                عرض
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-center mt-4">
                        {{ $jobs->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

{{-- Mobile Filter Offcanvas --}}
<div class="offcanvas offcanvas-bottom d-lg-none" tabindex="-1" id="mobileFilters"
     style="height:75vh;border-radius:20px 20px 0 0">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold">فلترة الوظائف</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body overflow-auto">

        <div class="filter-group mb-3">
            <div class="filter-title">المدينة</div>
            @foreach($cities as $city)
                <a href="{{ request()->fullUrlWithQuery(['location' => ($filters['location'] ?? '') === $city ? null : $city]) }}"
                   class="filter-chip {{ ($filters['location'] ?? '') === $city ? 'active' : '' }}">
                    {{ $city }}
                </a>
            @endforeach
        </div>

        <div class="filter-group mb-3">
            <div class="filter-title">نوع الدوام</div>
            @foreach(['full_time'=>'دوام كامل','part_time'=>'دوام جزئي','remote'=>'عن بُعد','contract'=>'عقد','freelance'=>'عمل حر'] as $v=>$l)
                <a href="{{ request()->fullUrlWithQuery(['job_type' => ($filters['job_type'] ?? '') === $v ? null : $v]) }}"
                   class="filter-chip {{ ($filters['job_type'] ?? '') === $v ? 'active' : '' }}">
                    {{ $l }}
                </a>
            @endforeach
        </div>

        @if(array_filter($filters))
            <a href="{{ route('jobs.index') }}" class="btn btn-outline-danger btn-sm w-100 rounded-pill mt-2">
                <i class="fas fa-times me-1"></i>مسح الفلاتر
            </a>
        @endif

    </div>
</div>

@endsection
