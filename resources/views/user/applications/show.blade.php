@extends('layouts.app')
@section('title', 'تفاصيل الطلب')

@push('styles')
<style>
    .page-wrap { padding: 1.5rem 0 3rem; }

    /* Timeline */
    .timeline-wrap { position:relative; padding-right:1.5rem; }
    .timeline-wrap::before {
        content:''; position:absolute; right:.45rem; top:0; bottom:0;
        width:2px; background:#e9ecef;
    }
    .tl-item { position:relative; padding-bottom:1.25rem; }
    .tl-item:last-child { padding-bottom:0; }
    .tl-dot {
        position:absolute; right:-.9rem; top:.2rem;
        width:18px; height:18px; border-radius:50%;
        background:#2C5AA0; border:3px solid #fff;
        box-shadow:0 0 0 2px #2C5AA0;
    }
    .tl-dot.latest { background:#F8B500; box-shadow:0 0 0 2px #F8B500; }
    .tl-dot.rejected { background:#dc3545; box-shadow:0 0 0 2px #dc3545; }
    .tl-content { margin-right:.75rem; }
    .tl-status { font-weight:700; font-size:.88rem; color:#333; }
    .tl-note   { font-size:.78rem; color:#666; margin-top:.2rem; }
    .tl-time   { font-size:.72rem; color:#aaa; margin-top:.2rem; }
</style>
@endpush

@section('content')
<div class="page-wrap">
<div class="container">

    <div class="mb-3">
        <a href="{{ route('user.applications.index') }}"
           class="text-muted text-decoration-none" style="font-size:.88rem">
            <i class="fas fa-arrow-right me-1"></i>العودة لطلباتي
        </a>
    </div>

    <div class="row g-3">

        {{-- Main --}}
        <div class="col-lg-8">

            {{-- معلومات الوظيفة --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <div class="d-flex gap-3 align-items-start">
                        <img src="{{ $application->job->company->logo_url }}"
                             style="width:60px;height:60px;border-radius:12px;object-fit:cover;border:1px solid #eee"
                             alt="">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <h5 class="fw-bold mb-1">{{ $application->job->title }}</h5>
                                <span class="badge bg-{{ $application->status_color }} flex-shrink-0 px-3 py-2"
                                      style="font-size:.82rem">
                                    {{ $application->status_label }}
                                </span>
                            </div>
                            <div class="text-primary mb-2">
                                <i class="fas fa-building me-1"></i>
                                {{ $application->job->company->company_name }}
                            </div>
                            <div class="d-flex flex-wrap gap-3 text-muted" style="font-size:.8rem">
                                @if($application->job->location)
                                    <span><i class="fas fa-map-marker-alt me-1"></i>{{ $application->job->location }}</span>
                                @endif
                                <span><i class="fas fa-briefcase me-1"></i>{{ $application->job->job_type_label ?? '' }}</span>
                                <span><i class="fas fa-calendar me-1"></i>تقدّمت {{ $application->applied_at?->format('Y/m/d') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- رسالة التغطية --}}
            @if($application->cover_letter)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent fw-bold">
                    <i class="fas fa-file-alt me-2 text-primary"></i>رسالة التغطية
                </div>
                <div class="card-body">
                    <p class="mb-0" style="line-height:1.9;white-space:pre-wrap;color:#444">
                        {{ $application->cover_letter }}
                    </p>
                </div>
            </div>
            @endif

            {{-- ملف CV --}}
            @if($application->cv_url)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded p-2 bg-danger bg-opacity-10">
                        <i class="fas fa-file-pdf text-danger fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">السيرة الذاتية المرفقة</div>
                        <div class="text-muted small">PDF</div>
                    </div>
                    <a href="{{ $application->cv_url }}" target="_blank"
                       class="btn btn-sm btn-outline-danger rounded-pill px-3">
                        <i class="fas fa-download me-1"></i>تحميل
                    </a>
                </div>
            </div>
            @endif

            {{-- وصف الوظيفة --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent fw-bold">
                    <i class="fas fa-info-circle me-2 text-primary"></i>عن الوظيفة
                </div>
                <div class="card-body">
                    <p style="line-height:1.9;color:#444;white-space:pre-wrap">
                        {{ Str::limit($application->job->description, 400) }}
                    </p>
                    <a href="{{ route('jobs.show', $application->job_id) }}"
                       class="btn btn-sm btn-outline-primary rounded-pill px-3"
                       target="_blank">
                        عرض الوظيفة كاملاً
                    </a>
                </div>
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">

            {{-- تتبع الحالة --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent fw-bold">
                    <i class="fas fa-history me-2 text-primary"></i>تتبع الطلب
                </div>
                <div class="card-body">
                    @if($application->statusHistory->isNotEmpty())
                        <div class="timeline-wrap">
                            @foreach($application->statusHistory->reverse() as $i => $h)
                            <div class="tl-item">
                                <div class="tl-dot {{ $i === 0 ? ($h->status === 'rejected' ? 'rejected' : 'latest') : '' }}"></div>
                                <div class="tl-content">
                                    <div class="tl-status">{{ $h->status_label }}</div>
                                    @if($h->note)
                                        <div class="tl-note">{{ $h->note }}</div>
                                    @endif
                                    <div class="tl-time">{{ $h->changed_at->format('Y/m/d — H:i') }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-3" style="font-size:.85rem">
                            <i class="fas fa-clock fa-2x mb-2 d-block opacity-25"></i>
                            في انتظار مراجعة الشركة
                        </div>
                    @endif
                </div>
            </div>

            {{-- معلومات الشركة --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent fw-bold">
                    <i class="fas fa-building me-2 text-primary"></i>معلومات الشركة
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 align-items-center mb-2">
                        <img src="{{ $application->job->company->logo_url }}"
                             style="width:44px;height:44px;border-radius:10px;object-fit:cover" alt="">
                        <div>
                            <div class="fw-semibold" style="font-size:.9rem">
                                {{ $application->job->company->company_name }}
                            </div>
                            @if($application->job->company->is_verified)
                                <span style="font-size:.72rem;color:#28a745">
                                    <i class="fas fa-check-circle me-1"></i>موثّقة
                                </span>
                            @endif
                        </div>
                    </div>
                    @if($application->job->company->industry)
                        <div class="text-muted" style="font-size:.78rem">
                            <i class="fas fa-industry me-1"></i>{{ $application->job->company->industry }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- سحب الطلب --}}
            @if(in_array($application->status, ['pending','viewed']))
            <div class="card border-0 shadow-sm border-danger border-opacity-25">
                <div class="card-body p-3">
                    <p class="text-muted small mb-2">
                        يمكنك سحب طلبك طالما لم تصل لمرحلة الاختصار.
                    </p>
                    <form action="{{ route('user.applications.destroy', $application) }}"
                          method="POST"
                          onsubmit="return confirm('هل أنت متأكد من سحب هذا الطلب؟')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm w-100 rounded-pill">
                            <i class="fas fa-undo me-2"></i>سحب الطلب
                        </button>
                    </form>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
</div>
@endsection
