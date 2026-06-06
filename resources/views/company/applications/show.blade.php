@extends('layouts.company')

@section('title', 'تفاصيل الطلب')
@section('page-title', 'تفاصيل الطلب')

@section('content')
@php
    $snapshot = $application->resume_snapshot ?? [];
    $identity = $snapshot['identity'] ?? [];
    $snapshotSkills = collect($snapshot['skills'] ?? []);
    $snapshotExperiences = collect($snapshot['experiences'] ?? []);
    $snapshotEducations = collect($snapshot['educations'] ?? []);
    $snapshotProjects = collect($snapshot['projects'] ?? []);
    $snapshotCertifications = collect($snapshot['certifications'] ?? []);
    $snapshotLanguages = collect($snapshot['languages'] ?? []);
    $snapshotLinks = collect($identity['links'] ?? [])->filter();
    $snapshotLinkLabels = [
        'linkedin' => 'لينكدإن',
        'github' => 'GitHub',
        'portfolio' => 'المعرض الشخصي',
    ];

@endphp
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
                        <h5 class="mb-1 fw-bold">{{ $application->candidate_name }}</h5>
                        @if($application->candidate_headline)
                            <div class="small text-primary fw-semibold mb-1">{{ $application->candidate_headline }}</div>
                        @endif
                        <div class="text-muted small">
                            <i class="fas fa-envelope me-1"></i>{{ $application->candidate_email }}
                            @if($application->candidate_phone)
                                &ensp;<i class="fas fa-phone me-1"></i>{{ $application->candidate_phone }}
                            @endif
                        </div>
                        @if($application->candidate_location)
                            <div class="text-muted small mt-1">
                                <i class="fas fa-map-marker-alt me-1"></i>{{ $application->candidate_location }}
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

                @if($snapshotLinks->isNotEmpty())
                <div class="d-flex gap-2 flex-wrap mb-3">
                    @foreach($snapshotLinks as $type => $url)
                        <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="fas fa-link me-1"></i>{{ $snapshotLinkLabels[$type] ?? $type }}
                        </a>
                    @endforeach
                </div>
                @endif

                @if($snapshotSkills->isNotEmpty())
                    <div class="small mb-2">
                        <strong>المهارات:</strong>
                        {{ $application->snapshot_skills_summary }}
                    </div>
                @endif

                @if($identity['summary'] ?? null)
                    <p class="text-muted small mb-0">{{ $identity['summary'] }}</p>
                @endif
            </div>
        </div>

        @if($application->cover_letter)
        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-file-alt me-2 text-primary"></i>رسالة التغطية</div>
            <div class="card-body">
                <p class="mb-0" style="line-height:1.9;white-space:pre-wrap">
                    {{ $application->cover_letter }}
                </p>
            </div>
        </div>
        @endif

        @if($application->submitted_resume_pdf_url)
        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-file-pdf me-2 text-danger"></i>السيرة الذاتية</div>
            <div class="card-body">
                <a href="{{ $application->submitted_resume_pdf_url }}" target="_blank"
                   class="btn btn-outline-danger rounded-pill px-4">
                    <i class="fas fa-download me-2"></i>تحميل / عرض السيرة
                </a>
            </div>
        </div>
        @endif

        @if($snapshot)
        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-id-card me-2 text-primary"></i>نسخة السيرة وقت التقديم</div>
            <div class="card-body">
                <div class="row g-2 small mb-3">
                    <div class="col-md-4 text-muted">وقت حفظ النسخة</div>
                    <div class="col-md-8 fw-semibold">{{ $application->resume_snapshot_created_at?->format('Y/m/d H:i') ?? 'غير متاح' }}</div>
                    <div class="col-md-4 text-muted">إصدار النسخة</div>
                    <div class="col-md-8 fw-semibold">v{{ $application->resume_snapshot_version }}</div>
                    <div class="col-md-4 text-muted">السيرة المستخدمة</div>
                    <div class="col-md-8 fw-semibold">{{ $application->resume?->title ?? 'نسخة من الملف المهني وقت التقديم' }}</div>
                </div>

                @if($snapshotExperiences->isNotEmpty())
                    <h6 class="fw-bold mb-2">الخبرات</h6>
                    @foreach($snapshotExperiences->take(4) as $experience)
                        <div class="border rounded p-2 mb-2 small">
                            <div class="fw-semibold">{{ $experience['title'] ?? '' }} @if($experience['company_name'] ?? null) - {{ $experience['company_name'] }} @endif</div>
                            @if($experience['summary'] ?? null)
                                <div class="text-muted">{{ $experience['summary'] }}</div>
                            @endif
                        </div>
                    @endforeach
                @endif

                @if($snapshotEducations->isNotEmpty())
                    <h6 class="fw-bold mb-2 mt-3">التعليم</h6>
                    @foreach($snapshotEducations->take(3) as $education)
                        <div class="small mb-1">{{ $education['degree'] ?? '' }} @if($education['institution_name'] ?? null) - {{ $education['institution_name'] }} @endif</div>
                    @endforeach
                @endif

                @if($snapshotProjects->isNotEmpty())
                    <h6 class="fw-bold mb-2 mt-3">المشاريع</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($snapshotProjects->take(5) as $project)
                            <span class="badge bg-light text-dark border">{{ $project['title'] ?? 'مشروع' }}</span>
                        @endforeach
                    </div>
                @endif

                @if($snapshotCertifications->isNotEmpty() || $snapshotLanguages->isNotEmpty())
                    <hr>
                    <div class="small text-muted">
                        الشهادات: {{ $snapshotCertifications->pluck('name')->filter()->implode(', ') ?: 'غير متاح' }}<br>
                        اللغات: {{ $snapshotLanguages->pluck('name')->filter()->implode(', ') ?: 'غير متاح' }}
                    </div>
                @endif
            </div>
        </div>
        @endif

        <div class="card mt-3">
            <div class="card-header"><i class="fas fa-notes-medical me-2 text-primary"></i>ملاحظات التوظيف</div>
            <div class="card-body">
                <form action="{{ route('company.applications.notes.store', $application) }}" method="POST">
                    @csrf
                    <textarea name="body" class="form-control mb-2" rows="3" placeholder="ملاحظة داخلية لفريق التوظيف..." required></textarea>
                    <button class="btn btn-sm btn-primary rounded-pill px-4">إضافة ملاحظة</button>
                </form>

                @foreach($application->atsNotes as $note)
                    <div class="border-top mt-3 pt-3">
                        <div class="small text-muted">{{ $note->created_at?->format('Y/m/d H:i') }} · {{ $note->company?->company_name }}</div>
                        <div style="white-space:pre-wrap">{{ $note->body }}</div>
                    </div>
                @endforeach
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
                @php($transitions = $workflow->availableTransitions($application))
                @if($transitions)
                    <form action="{{ route('company.applications.transitionStatus', $application) }}" method="POST">
                        @csrf @method('PATCH')
                        <select name="status" class="form-select form-select-sm mb-2">
                            <option value="">اختر الانتقال التالي...</option>
                            @foreach($transitions as $transition)
                                <option value="{{ $transition['status'] }}">{{ $transition['label'] }}</option>
                            @endforeach
                        </select>
                        <textarea name="note" class="form-control form-control-sm mb-2" rows="2"
                                  placeholder="ملاحظة مع التغيير (اختياري)"></textarea>
                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill">
                            <i class="fas fa-save me-1"></i>حفظ التغيير
                        </button>
                    </form>
                @else
                    <div class="text-muted small">هذه حالة نهائية ولا توجد انتقالات متاحة.</div>
                @endif
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
                        @if($h->from_status)
                            <div class="text-muted" style="font-size:.72rem">
                                من {{ $workflow->statusLabel($h->from_status) }} إلى {{ $workflow->statusLabel($h->status) }}
                            </div>
                        @endif
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

        @if($application->latestAiMatch)
        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-wand-magic-sparkles me-2 text-primary"></i>AI Match Snapshot</div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-3">
                    <div>
                        <div class="small text-muted">Overall match</div>
                        <div class="fw-bold">{{ number_format((float) $application->latestAiMatch->overall_score, 1) }}/100</div>
                    </div>
                    <div class="text-end small text-muted">
                        Snapshot v{{ $application->latestAiMatch->resume_snapshot_version }}<br>
                        {{ $application->latestAiMatch->evaluated_at?->format('Y/m/d H:i') }}
                    </div>
                </div>

                @if($application->latestAiMatch->ai_explanation)
                    <p class="small text-muted mb-3">{{ $application->latestAiMatch->ai_explanation }}</p>
                @endif

                <div class="small mb-2">
                    <strong>Matched skills:</strong>
                    {{ collect($application->latestAiMatch->matched_skills ?? [])->implode(', ') ?: 'None' }}
                </div>
                <div class="small mb-2">
                    <strong>Missing skills:</strong>
                    {{ collect($application->latestAiMatch->missing_skills ?? [])->implode(', ') ?: 'None' }}
                </div>
                @if($application->latestAiMatch->is_reused)
                    <span class="badge bg-light text-dark border">Reused from match #{{ $application->latestAiMatch->reused_from_match_id }}</span>
                @endif
            </div>
        </div>
        @endif

        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-clipboard-check me-2 text-primary"></i>تقييم المتقدم</div>
            <div class="card-body">
                @php($review = $application->reviews->first())
                @if($review?->overall_score !== null)
                    <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-3">
                        <div>
                            <div class="small text-muted">الدرجة الإجمالية</div>
                            <div class="fw-bold">{{ number_format((float) $review->overall_score, 1) }}/100</div>
                        </div>
                        <div class="text-end small text-muted">
                            نسخة السيرة v{{ $review->evaluated_snapshot_version ?? $application->resume_snapshot_version }}<br>
                            {{ $review->evaluated_at?->format('Y/m/d H:i') }}
                        </div>
                    </div>
                @endif
                <form action="{{ route('company.applications.review', $application) }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small">التقييم</label>
                            <select name="rating" class="form-select form-select-sm">
                                <option value="">--</option>
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" @selected((int) old('rating', $review?->rating) === $i)>{{ $i }}/5</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">التوصية</label>
                            <select name="recommendation" class="form-select form-select-sm">
                                @foreach(['' => '--', 'strong_yes' => 'مناسب جدًا', 'yes' => 'مناسب', 'maybe' => 'بحاجة لمراجعة', 'no' => 'غير مناسب', 'strong_no' => 'غير مناسب إطلاقًا'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('recommendation', $review?->recommendation) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small mt-1">معايير التقييم</label>
                            <div class="row g-2 mb-2">
                                @foreach([
                                    'technical_fit' => 'الملاءمة التقنية',
                                    'experience_fit' => 'ملاءمة الخبرة',
                                    'role_fit' => 'ملاءمة الدور',
                                    'communication' => 'التواصل',
                                ] as $key => $label)
                                    <div class="col-6">
                                        <select name="rubric_scores[{{ $key }}]" class="form-select form-select-sm">
                                            <option value="">{{ $label }}</option>
                                            @for($score = 1; $score <= 5; $score++)
                                                <option value="{{ $score }}" @selected((int) old("rubric_scores.$key", data_get($review?->rubric_scores, $key)) === $score)>
                                                    {{ $label }}: {{ $score }}/5
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                            <textarea name="strengths" class="form-control form-control-sm mb-2" rows="2" placeholder="نقاط القوة">{{ old('strengths', $review?->strengths) }}</textarea>
                            <textarea name="concerns" class="form-control form-control-sm" rows="2" placeholder="الملاحظات أو المخاوف">{{ old('concerns', $review?->concerns) }}</textarea>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-primary rounded-pill w-100 mt-2">حفظ التقييم</button>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-calendar-check me-2 text-primary"></i>جدولة المقابلة</div>
            <div class="card-body">
                <form action="{{ route('company.applications.interviews.store', $application) }}" method="POST">
                    @csrf
                    <input type="datetime-local" name="scheduled_at" class="form-control form-control-sm mb-2" required>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="number" name="duration_minutes" class="form-control form-control-sm" value="30" min="10" max="480">
                        </div>
                        <div class="col-6">
                            <select name="location_type" class="form-select form-select-sm">
                                <option value="online">عن بعد</option>
                                <option value="onsite">حضوري</option>
                                <option value="phone">هاتف</option>
                            </select>
                        </div>
                    </div>
                    <input type="url" name="meeting_url" class="form-control form-control-sm mt-2" placeholder="رابط الاجتماع">
                    <input type="text" name="location" class="form-control form-control-sm mt-2" placeholder="الموقع">
                    <textarea name="notes" class="form-control form-control-sm mt-2" rows="2" placeholder="ملاحظات المقابلة"></textarea>
                    <button class="btn btn-sm btn-outline-primary rounded-pill w-100 mt-2">جدولة المقابلة</button>
                </form>

                @foreach($application->interviews as $interview)
                    @php($locationTypeLabel = ['online' => 'عن بعد', 'onsite' => 'حضوري', 'phone' => 'هاتف'][$interview->location_type] ?? $interview->location_type)
                    <div class="border-top mt-3 pt-2 small">
                        <div class="fw-semibold">{{ $interview->scheduled_at?->format('Y/m/d H:i') }} - {{ $interview->duration_minutes }} دقيقة</div>
                        <div class="text-muted">{{ $locationTypeLabel }} {{ $interview->meeting_url ? '- '.$interview->meeting_url : '' }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-route me-2 text-primary"></i>سجل أنشطة التوظيف</div>
            <div class="card-body p-0">
                @forelse($application->activities as $activity)
                    <div class="p-3 border-bottom small">
                        <div class="fw-semibold">{{ $activity->description }}</div>
                        <div class="text-muted">{{ $activity->occurred_at?->format('Y/m/d H:i') }}</div>
                    </div>
                @empty
                    <div class="p-3 text-muted small">لا توجد أنشطة توظيف حتى الآن.</div>
                @endforelse
            </div>
        </div>

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
