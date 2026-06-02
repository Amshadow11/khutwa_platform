@extends('layouts.company')

@section('title', 'مسار التوظيف')
@section('page-title', 'مسار التوظيف')

@push('styles')
<style>
    .pipeline-toolbar { display:flex; gap:.75rem; align-items:center; justify-content:space-between; flex-wrap:wrap; margin-bottom:1rem; }
    .pipeline-board { display:grid; grid-auto-flow:column; grid-auto-columns:minmax(280px, 1fr); gap:1rem; overflow-x:auto; padding-bottom:.5rem; }
    .pipeline-column { background:#f8fafc; border:1px solid #e9eef5; border-radius:12px; min-height:520px; }
    .pipeline-column-header { padding:1rem; border-bottom:1px solid #e9eef5; display:flex; align-items:center; justify-content:space-between; gap:.75rem; }
    .pipeline-column-body { padding:.75rem; }
    .candidate-card { background:#fff; border-radius:10px; padding:.9rem; box-shadow:0 1px 8px rgba(15,23,42,.06); margin-bottom:.75rem; border:1px solid #eef2f7; }
    .candidate-avatar { width:38px; height:38px; border-radius:50%; object-fit:cover; background:#e9ecef; }
    .candidate-title { font-size:.88rem; font-weight:700; color:#1f2937; }
    .candidate-meta { color:#6b7280; font-size:.75rem; }
    .quick-actions { display:grid; grid-template-columns:1fr 1fr; gap:.35rem; margin-top:.75rem; }
    .quick-actions .btn { font-size:.72rem; padding:.25rem .45rem; }
    .rating-stars { color:#f59e0b; font-size:.8rem; }
    @media (max-width: 991px) { .pipeline-board { grid-auto-columns: 86vw; } }
</style>
@endpush

@section('content')
@php
    $recommendationLabels = [
        'strong_yes' => 'مناسب جدًا',
        'yes' => 'مناسب',
        'maybe' => 'بحاجة لمراجعة',
        'no' => 'غير مناسب',
        'strong_no' => 'غير مناسب إطلاقًا',
    ];
@endphp

<div class="pipeline-toolbar">
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('company.applications.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="fas fa-list me-1"></i>القائمة
        </a>
        <a href="{{ route('company.applications.pipeline') }}" class="btn btn-sm btn-primary rounded-pill px-3">
            <i class="fas fa-columns me-1"></i>مسار التوظيف
        </a>
    </div>

    <form method="GET" action="{{ route('company.applications.pipeline') }}" class="d-flex gap-2 align-items-center flex-wrap">
        <select name="job_id" class="form-select form-select-sm" style="min-width:220px" onchange="this.form.submit()">
            <option value="">كل الوظائف</option>
            @foreach($jobs as $job)
                <option value="{{ $job->id }}" @selected((string) request('job_id') === (string) $job->id)>
                    {{ Str::limit($job->title, 44) }}
                </option>
            @endforeach
        </select>
        @if(request('job_id'))
            <a href="{{ route('company.applications.pipeline') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">مسح</a>
        @endif
    </form>
</div>

<div class="pipeline-board">
    @foreach($columns as $column)
        <section class="pipeline-column">
            <div class="pipeline-column-header">
                <div>
                    <div class="fw-bold">{{ $column['label'] }}</div>
                    <div class="small text-muted">مرحلة توظيف</div>
                </div>
                <span class="badge bg-{{ $column['color'] }}">{{ $column['applications']->count() }}</span>
            </div>
            <div class="pipeline-column-body">
                @forelse($column['applications'] as $application)
                    @php
                        $review = $application->reviews->first();
                        $nextInterview = $application->interviews->sortBy('scheduled_at')->first();
                    @endphp
                    <article class="candidate-card">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $application->user->avatar_url }}" class="candidate-avatar" alt="">
                            <div class="min-w-0">
                                <div class="candidate-title">{{ $application->candidate_name }}</div>
                                @if($application->candidate_headline)
                                    <div class="candidate-meta">{{ $application->candidate_headline }}</div>
                                @endif
                                <div class="candidate-meta">{{ $application->job->title ?? '—' }}</div>
                            </div>
                        </div>

                        <div class="candidate-meta mt-2">
                            <i class="fas fa-clock me-1"></i>{{ $application->applied_at?->diffForHumans() }}
                            @if($application->candidate_location)
                                <span class="mx-1">·</span><i class="fas fa-location-dot me-1"></i>{{ $application->candidate_location }}
                            @endif
                            @if($application->snapshot_skills_summary)
                                <div class="mt-1"><i class="fas fa-code me-1"></i>{{ $application->snapshot_skills_summary }}</div>
                            @endif
                            @if(! $application->candidate_location && $application->job?->location)
                                <span class="mx-1">·</span><i class="fas fa-location-dot me-1"></i>{{ $application->job->location }}
                            @endif
                        </div>

                        @if($review?->rating)
                            <div class="rating-stars mt-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                @endfor
                                <span class="text-muted small ms-1">{{ $recommendationLabels[$review->recommendation] ?? '' }}</span>
                            </div>
                        @endif

                        @if($review?->overall_score !== null)
                            <div class="small mt-2">
                                <span class="badge bg-primary">{{ number_format((float) $review->overall_score, 1) }}/100</span>
                                <span class="text-muted">درجة التقييم</span>
                            </div>
                        @endif

                        @if($nextInterview)
                            <div class="alert alert-light border py-1 px-2 mt-2 mb-0 small">
                                <i class="fas fa-calendar-check me-1 text-primary"></i>
                                {{ $nextInterview->scheduled_at?->format('Y/m/d H:i') }}
                            </div>
                        @endif

                        <div class="quick-actions">
                            <a href="{{ route('company.applications.show', $application) }}" class="btn btn-outline-primary rounded-pill">
                                عرض
                            </a>
                            @foreach($stages as $status => $stage)
                                @continue($status === $application->status)
                                @if(in_array($status, ['shortlisted', 'interview', 'accepted', 'rejected'], true))
                                    <form action="{{ route('company.applications.updateStatus', $application) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $status }}">
                                        <button class="btn btn-outline-secondary rounded-pill w-100">{{ $stage['label'] }}</button>
                                    </form>
                                @endif
                            @endforeach
                        </div>
                    </article>
                @empty
                    <div class="text-center text-muted small py-4">لا توجد طلبات في هذه المرحلة.</div>
                @endforelse
            </div>
        </section>
    @endforeach
</div>
@endsection
