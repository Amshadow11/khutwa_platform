@extends('layouts.company')

@section('title', 'تفاصيل الطلب')
@section('page-title', 'تفاصيل الطلب')

@section('content')
<div class="row g-3">

    {{-- العمود الرئيسي --}}
    <div class="col-lg-8">

        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-user me-2 text-primary"></i>بيانات المتقدم</div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ $application->user->avatar_url }}"
                         style="width:64px;height:64px;border-radius:50%;object-fit:cover" alt="">
                    <div>
                        <h5 class="mb-1 fw-bold">{{ $application->user->display_name }}</h5>
                        <div class="text-muted small">
                            <i class="fas fa-envelope me-1"></i>{{ $application->user->email }}
                            @if($application->user->phone)
                                &ensp;<i class="fas fa-phone me-1"></i>{{ $application->user->phone }}
                            @endif
                        </div>
                        @if($application->user->address)
                            <div class="text-muted small mt-1">
                                <i class="fas fa-map-marker-alt me-1"></i>{{ $application->user->address }}
                            </div>
                        @endif

                        <div class="mt-3">
                            <form action="{{ route('messages.start') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="company_id" value="{{ auth('company')->id() }}">
                                <input type="hidden" name="user_id" value="{{ $application->user_id }}">
                                <input type="hidden" name="job_id" value="{{ $application->job_id }}">
                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4">
                                    <i class="fas fa-comment-dots me-1"></i>مراسلة المتقدم
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                @if($application->user->linkedin_url || $application->user->github_url || $application->user->portfolio_url)
                <div class="d-flex gap-2 flex-wrap mb-3">
                    @if($application->user->linkedin_url)
                        <a href="{{ $application->user->linkedin_url }}" target="_blank"
                           class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="fab fa-linkedin me-1"></i>LinkedIn
                        </a>
                    @endif
                    @if($application->user->github_url)
                        <a href="{{ $application->user->github_url }}" target="_blank"
                           class="btn btn-sm btn-outline-dark rounded-pill px-3">
                            <i class="fab fa-github me-1"></i>GitHub
                        </a>
                    @endif
                    @if($application->user->portfolio_url)
                        <a href="{{ $application->user->portfolio_url }}" target="_blank"
                           class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            <i class="fas fa-globe me-1"></i>Portfolio
                        </a>
                    @endif
                </div>
                @endif

                @if($application->user->skills)
                    <div class="small mb-2">
                        <strong>المهارات:</strong> {{ $application->user->skills }}
                    </div>
                @endif

                @if($application->user->bio)
                    <p class="text-muted small mb-0">{{ $application->user->bio }}</p>
                @endif
            </div>
        </div>

        @if($application->cover_letter || $application->about)
        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-file-alt me-2 text-primary"></i>رسالة التغطية</div>
            <div class="card-body">
                <p class="mb-0" style="line-height:1.9;white-space:pre-wrap">
                    {{ $application->cover_letter ?? $application->about }}
                </p>
            </div>
        </div>
        @endif

        @if($application->cv_url)
        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-file-pdf me-2 text-danger"></i>السيرة الذاتية</div>
            <div class="card-body">
                <a href="{{ $application->cv_url }}" target="_blank"
                   class="btn btn-outline-danger rounded-pill px-4">
                    <i class="fas fa-download me-2"></i>تحميل / عرض CV
                </a>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header"><i class="fas fa-sticky-note me-2 text-warning"></i>ملاحظات داخلية</div>
            <div class="card-body">
                <form action="{{ route('company.applications.updateStatus', $application) }}" method="POST">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ $application->status }}">
                    <textarea name="note" class="form-control mb-2" rows="3"
                              placeholder="ملاحظة داخلية لا تظهر للمتقدم..."
                              style="font-size:.88rem">{{ $application->notes }}</textarea>
                    <button type="submit" class="btn btn-sm btn-warning rounded-pill px-4">
                        <i class="fas fa-save me-1"></i>حفظ الملاحظة
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- العمود الجانبي --}}
    <div class="col-lg-4">

        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-briefcase me-2 text-primary"></i>الوظيفة</div>
            <div class="card-body">
                <h6 class="fw-bold mb-1">{{ $application->job->title }}</h6>
                <div class="text-muted small">
                    <i class="fas fa-map-marker-alt me-1"></i>{{ $application->job->location ?? 'غير محدد' }}<br>
                    <i class="fas fa-clock me-1"></i>{{ $application->job->job_type_label ?? '' }}
                </div>
                <hr class="my-2">
                <div class="small text-muted">
                    تاريخ التقديم:
                    <strong>{{ $application->applied_at?->format('Y/m/d — H:i') }}</strong>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-tasks me-2 text-primary"></i>تحديث الحالة</div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="badge bg-{{ $application->status_color }} px-3 py-2" style="font-size:.85rem">
                        {{ $application->status_label }}
                    </span>
                </div>
                <form action="{{ route('company.applications.updateStatus', $application) }}" method="POST">
                    @csrf @method('PATCH')
                    <select name="status" class="form-select form-select-sm mb-2">
                        @foreach([
                            'pending'=>'قيد المراجعة','viewed'=>'تمت المشاهدة',
                            'shortlisted'=>'في القائمة المختصرة','interview'=>'دُعي للمقابلة',
                            'accepted'=>'مقبول','rejected'=>'مرفوض',
                        ] as $v=>$l)
                            <option value="{{ $v }}" {{ $application->status===$v?'selected':'' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                    <textarea name="note" class="form-control form-control-sm mb-2" rows="2"
                              placeholder="ملاحظة مع التغيير (اختياري)"></textarea>
                    <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill">
                        <i class="fas fa-save me-1"></i>حفظ التغيير
                    </button>
                </form>
            </div>
        </div>

        @if($application->statusHistory->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-history me-2 text-primary"></i>سجل التغييرات</div>
            <div class="card-body p-0">
                @foreach($application->statusHistory as $h)
                <div class="d-flex gap-2 p-3 border-bottom">
                    <div class="flex-shrink-0" style="padding-top:4px">
                        <i class="fas fa-circle" style="font-size:.5rem;color:#2C5AA0"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="font-size:.82rem">{{ $h->status_label }}</div>
                        @if($h->note)
                            <div class="text-muted" style="font-size:.75rem">{{ $h->note }}</div>
                        @endif
                        <div class="text-muted" style="font-size:.72rem">
                            {{ $h->changed_at->format('Y/m/d H:i') }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="d-flex gap-2">
            <a href="{{ route('company.applications.index') }}"
               class="btn btn-outline-secondary rounded-pill flex-fill btn-sm">
                <i class="fas fa-arrow-right me-1"></i>رجوع
            </a>
            <a href="{{ route('company.jobs.show', $application->job_id) }}"
               class="btn btn-outline-primary rounded-pill flex-fill btn-sm">
                <i class="fas fa-users me-1"></i>كل الطلبات
            </a>
        </div>

    </div>
</div>
@endsection
