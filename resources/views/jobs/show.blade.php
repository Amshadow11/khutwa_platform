@extends('layouts.app')

@section('title', $job->title)
@section('description', Str::limit(strip_tags($job->description), 160))

@push('styles')
<style>
    .job-detail { padding: 2rem 0 3rem; }
    .detail-card { border: none; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
    .company-logo-xl {
        width: 80px; height: 80px; border-radius: 16px;
        object-fit: cover; border: 2px solid #eee;
        background: #f4f6fb;
    }
    .info-badge {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #f4f6fb; color: #555;
        font-size: .82rem; padding: .4rem .9rem; border-radius: 20px;
    }
    .section-heading {
        font-weight: 700; font-size: 1rem; color: #222;
        border-right: 3px solid #2C5AA0; padding-right: .75rem;
        margin-bottom: 1rem;
    }
    .apply-sticky {
        position: sticky; top: 80px;
    }
    .btn-apply {
        background: linear-gradient(135deg, #2C5AA0, #1e4085);
        border: none; border-radius: 10px; color: #fff;
        font-size: 1rem; font-weight: 700; padding: .85rem;
        width: 100%; font-family: inherit; cursor: pointer;
        transition: all .2s;
    }
    .btn-apply:hover { opacity: .9; transform: translateY(-1px); }
    .btn-apply:disabled { opacity: .6; cursor: not-allowed; transform: none; }
    .content-body p, .content-body li {
        line-height: 1.9; color: #444;
    }
    .content-body ul { padding-right: 1.5rem; }
</style>
@endpush

@section('content')
<div class="job-detail">
    <div class="container">
        <div class="row g-3">

            {{-- ===== Main Content ===== --}}
            <div class="col-lg-8">

                {{-- Header Card --}}
                <div class="detail-card card mb-3">
                    <div class="card-body p-4">
                        <div class="d-flex gap-3 align-items-start mb-3">
                            <img src="{{ $job->company->logo_url }}"
                                 class="company-logo-xl" alt="{{ $job->company->company_name }}">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <h1 style="font-size:1.4rem;font-weight:800;margin-bottom:.4rem">
                                        {{ $job->title }}
                                    </h1>
                                    @if($job->urgent)
                                        <span class="badge bg-danger flex-shrink-0">⚡ عاجلة</span>
                                    @endif
                                </div>
                                <div class="text-primary fw-semibold mb-2">
                                    <i class="fas fa-building me-1"></i>{{ $job->company->company_name }}
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="info-badge">
                                        <i class="fas fa-map-marker-alt text-primary"></i>
                                        {{ $job->location ?? 'غير محدد' }}
                                    </span>
                                    <span class="info-badge">
                                        <i class="fas fa-clock text-primary"></i>
                                        {{ $job->job_type_label }}
                                    </span>
                                    @if($job->salary)
                                        <span class="info-badge">
                                            <i class="fas fa-money-bill-wave text-success"></i>
                                            {{ $job->salary }}
                                        </span>
                                    @endif
                                    @if($job->remote_work)
                                        <span class="info-badge" style="color:#28a745">
                                            <i class="fas fa-wifi"></i> يقبل العمل عن بُعد
                                        </span>
                                    @endif
                                    @if($job->experience_level)
                                        <span class="info-badge">
                                            <i class="fas fa-layer-group text-primary"></i>
                                            {{ match($job->experience_level) {
                                                'junior'=>'مبتدئ','mid'=>'متوسط',
                                                'senior'=>'خبير','manager'=>'مدير',
                                                default=>$job->experience_level
                                            } }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- معلومات التاريخ --}}
                        <div class="d-flex gap-3 text-muted" style="font-size:.8rem">
                            <span><i class="fas fa-calendar me-1"></i>نُشر {{ $job->created_at->diffForHumans() }}</span>
                            @if($job->deadline)
                                <span>
                                    <i class="fas fa-hourglass-end me-1"></i>
                                    آخر موعد: {{ $job->deadline->format('Y/m/d') }}
                                    @if($job->days_remaining !== null && $job->days_remaining <= 5)
                                        <span class="text-danger fw-semibold">({{ $job->days_remaining }} أيام متبقية)</span>
                                    @endif
                                </span>
                            @endif
                            <span><i class="fas fa-eye me-1"></i>{{ number_format($job->views) }} مشاهدة</span>
                        </div>
                    </div>
                </div>

                {{-- وصف الوظيفة --}}
                <div class="detail-card card mb-3">
                    <div class="card-body p-4">
                        <div class="section-heading">وصف الوظيفة</div>
                        <div class="content-body" style="line-height:1.9;white-space:pre-wrap">
                            {{ $job->description }}
                        </div>
                    </div>
                </div>

                {{-- المتطلبات --}}
                @if($job->requirements)
                <div class="detail-card card mb-3">
                    <div class="card-body p-4">
                        <div class="section-heading">المتطلبات والمؤهلات</div>
                        <div class="content-body" style="white-space:pre-wrap">
                            {{ $job->requirements }}
                        </div>
                    </div>
                </div>
                @endif

                {{-- المزايا --}}
                @if($job->benefits)
                <div class="detail-card card mb-3">
                    <div class="card-body p-4">
                        <div class="section-heading">المزايا والمكافآت</div>
                        <div class="content-body" style="white-space:pre-wrap">
                            {{ $job->benefits }}
                        </div>
                    </div>
                </div>
                @endif

                {{-- وظائف مشابهة --}}
                @if($relatedJobs->isNotEmpty())
                <div class="detail-card card">
                    <div class="card-header"><i class="fas fa-th-large me-2"></i>وظائف مشابهة</div>
                    <div class="card-body p-2">
                        @foreach($relatedJobs as $related)
                        <a href="{{ route('jobs.show', $related) }}"
                           class="d-flex gap-2 align-items-center p-2 rounded text-decoration-none"
                           style="transition:background .15s;"
                           onmouseover="this.style.background='#f8f9fa'"
                           onmouseout="this.style.background=''">
                            <img src="{{ $related->company->logo_url }}"
                                 style="width:40px;height:40px;border-radius:8px;object-fit:cover" alt="">
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-dark" style="font-size:.88rem">{{ $related->title }}</div>
                                <div class="text-muted" style="font-size:.75rem">{{ $related->company->company_name }}</div>
                            </div>
                            <i class="fas fa-arrow-left text-muted" style="font-size:.75rem"></i>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

            {{-- ===== Sidebar ===== --}}
            <div class="col-lg-4">
                <div class="apply-sticky">

                    {{-- بطاقة التقديم --}}
                    <div class="detail-card card mb-3">
                        <div class="card-body p-3">

                            @if($hasApplied)
                                <div class="text-center py-2">
                                    <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                                    <div class="fw-bold">تم التقديم على هذه الوظيفة</div>
                                    <small class="text-muted">تابع حالة طلبك من لوحة التحكم</small>
                                    <a href="{{ route('user.dashboard') }}"
                                       class="btn btn-outline-primary rounded-pill w-100 mt-2 btn-sm">
                                        متابعة الطلب
                                    </a>
                                </div>
                            @elseif($job->is_expired)
                                <button class="btn-apply" disabled>انتهى موعد التقديم</button>
                            @elseif(auth('web')->check())
                                <button class="btn-apply" onclick="openApplyModal()">
                                    <i class="fas fa-paper-plane me-2"></i>تقدم الآن
                                </button>
                            @else
                                <div class="text-center">
                                    <p class="text-muted small mb-2">سجّل دخولك للتقديم على هذه الوظيفة</p>
                                    <a href="{{ route('login') }}?redirect={{ url()->current() }}"
                                       class="btn-apply d-block text-center text-decoration-none"
                                       style="padding:.85rem;border-radius:10px;color:#fff">
                                        <i class="fas fa-sign-in-alt me-2"></i>تسجيل الدخول للتقديم
                                    </a>
                                    <a href="{{ route('register') }}"
                                       class="btn btn-outline-secondary rounded-pill w-100 mt-2 btn-sm">
                                        إنشاء حساب مجاني
                                    </a>
                                </div>
                            @endif

                        </div>
                    </div>

                    {{-- معلومات الشركة --}}
                    <div class="detail-card card">
                        <div class="card-header"><i class="fas fa-building me-2"></i>عن الشركة</div>
                        <div class="card-body p-3">
                            <div class="d-flex gap-2 align-items-center mb-2">
                                <img src="{{ $job->company->logo_url }}"
                                     style="width:44px;height:44px;border-radius:10px;object-fit:cover" alt="">
                                <div>
                                    <div class="fw-bold" style="font-size:.9rem">
                                        {{ $job->company->company_name }}
                                    </div>
                                    @if($job->company->is_verified)
                                        <span style="font-size:.72rem;color:#28a745">
                                            <i class="fas fa-check-circle me-1"></i>شركة موثّقة
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if($job->company->description)
                                <p class="text-muted small mb-2">
                                    {{ Str::limit($job->company->description, 120) }}
                                </p>
                            @endif
                            <div class="text-muted" style="font-size:.78rem">
                                @if($job->company->industry)
                                    <div><i class="fas fa-industry me-1"></i>{{ $job->company->industry }}</div>
                                @endif
                                @if($job->company->company_size)
                                    <div><i class="fas fa-users me-1"></i>{{ $job->company->company_size_label }}</div>
                                @endif
                                @if($job->company->website)
                                    <div>
                                        <i class="fas fa-globe me-1"></i>
                                        <a href="{{ $job->company->website }}" target="_blank"
                                           class="text-primary">{{ parse_url($job->company->website, PHP_URL_HOST) }}</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

{{-- Modal التقديم — سيُفعَّل في المرحلة 2 مع ApplicationController --}}
<div class="modal fade" id="applyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">التقديم على: {{ $job->title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="/apply/{{ $job->id }}" method="Post" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">رسالة التغطية (اختياري)</label>
                        <textarea name="cover_letter" class="form-control" rows="4"
                                  placeholder="اكتب رسالة قصيرة عن نفسك ولماذا تناسبك هذه الوظيفة..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">رفع CV (PDF)</label>
                        <input type="file" name="cv" class="form-control" accept=".pdf">
                        <div class="form-text">ملف PDF بحجم أقصى 5MB</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">
                        <i class="fas fa-paper-plane me-2"></i>إرسال الطلب
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openApplyModal() {
    new bootstrap.Modal(document.getElementById('applyModal')).show();
}
</script>
@endpush

@endsection
