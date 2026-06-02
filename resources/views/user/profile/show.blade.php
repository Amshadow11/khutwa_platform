@extends('layouts.app')
@section('title', 'ملفي المهني')

@push('styles')
<style>
    .profile-page { padding: 1.5rem 0 3rem; }
    .profile-shell { max-width: 980px; margin: 0 auto; }
    .profile-cover { height: 124px; background: linear-gradient(135deg, #2C5AA0, #16376f); border-radius: 12px 12px 0 0; }
    .profile-avatar { width: 96px; height: 96px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; margin-top: -48px; background: #e9ecef; }
    .identity-card { border: none; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.07); overflow: hidden; }
    .section-block { background: #fff; border-radius: 12px; padding: 1.25rem; box-shadow: 0 2px 12px rgba(0,0,0,.05); margin-bottom: 1rem; }
    .section-title { font-size: .9rem; font-weight: 800; color: #2C5AA0; margin-bottom: .8rem; }
    .skill-tag { display: inline-flex; align-items: center; gap: .35rem; background: rgba(44,90,160,.08); color: #2C5AA0; border-radius: 999px; padding: .3rem .75rem; font-size: .82rem; margin: .15rem; }
    .timeline-item { border-right: 2px solid rgba(44,90,160,.15); padding-right: 1rem; margin-bottom: 1rem; }
    .timeline-item:last-child { margin-bottom: 0; }
    .strength { background: #f8f9fa; border-radius: 10px; padding: 1rem; }
</style>
@endpush

@section('content')
@php
    $profile = $user->professionalProfile;
    $score = $completion['score'] ?? 0;
@endphp

<div class="profile-page">
    <div class="container profile-shell">
        <div class="identity-card bg-white mb-3">
            <div class="profile-cover"></div>
            <div class="p-4 pt-0">
                <img src="{{ $user->avatar_url }}" class="profile-avatar" alt="{{ $user->display_name }}">
                <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mt-2">
                    <div>
                        <h3 class="fw-bold mb-1">{{ $user->display_name }}</h3>
                        <div class="text-muted">{{ '@' . $user->username }}</div>
                        @if($profile?->headline)
                            <div class="fw-semibold mt-2">{{ $profile->headline }}</div>
                        @endif
                        @if($profile?->current_title || $profile?->current_company)
                            <div class="text-muted small">
                                {{ $profile?->current_title }}
                                @if($profile?->current_title && $profile?->current_company) · @endif
                                {{ $profile?->current_company }}
                            </div>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('profiles.public.show', $user->username) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i class="fas fa-share-nodes me-1"></i> الرابط العام
                        </a>
                        <a href="{{ route('user.resumes.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="fas fa-file-lines me-1"></i> السير الذاتية
                        </a>
                        <a href="{{ route('user.profile.edit') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                            <i class="fas fa-edit me-1"></i> تعديل
                        </a>
                    </div>
                </div>

                @if($user->bio)
                    <p class="text-muted mt-3 mb-0" style="line-height:1.8">{{ $user->bio }}</p>
                @endif
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                @if($user->canonicalSkills->isNotEmpty())
                    <div class="section-block">
                        <div class="section-title">المهارات</div>
                        @foreach($user->canonicalSkills as $skill)
                            <span class="skill-tag">
                                {{ $skill->name }}
                                @if($skill->pivot?->years_experience)
                                    <small>{{ $skill->pivot->years_experience }}y</small>
                                @endif
                            </span>
                        @endforeach
                    </div>
                @endif

                @if($user->experiences->isNotEmpty())
                    <div class="section-block">
                        <div class="section-title">الخبرات العملية</div>
                        @foreach($user->experiences as $experience)
                            <div class="timeline-item">
                                <div class="fw-bold">{{ $experience->title }}</div>
                                <div class="text-muted small">{{ $experience->company_name }} {{ $experience->is_current ? '· حاليًا' : '' }}</div>
                                <div class="text-muted small">
                                    {{ $experience->start_date?->format('Y/m') ?: 'غير محدد' }} -
                                    {{ $experience->is_current ? 'الآن' : ($experience->end_date?->format('Y/m') ?: 'غير محدد') }}
                                </div>
                                @if($experience->summary)
                                    <div class="mt-2 text-muted" style="white-space:pre-wrap">{{ $experience->summary }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($user->educations->isNotEmpty())
                    <div class="section-block">
                        <div class="section-title">التعليم</div>
                        @foreach($user->educations as $education)
                            <div class="timeline-item">
                                <div class="fw-bold">{{ $education->institution_name }}</div>
                                <div class="text-muted small">{{ $education->degree }} {{ $education->field_of_study ? '· '.$education->field_of_study : '' }}</div>
                                @if($education->description)
                                    <div class="mt-2 text-muted">{{ $education->description }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($user->projects->isNotEmpty())
                    <div class="section-block">
                        <div class="section-title">المشاريع</div>
                        @foreach($user->projects as $project)
                            <div class="mb-3">
                                <div class="fw-bold">{{ $project->title }}</div>
                                @if($project->description)<div class="text-muted">{{ $project->description }}</div>@endif
                                <div class="d-flex gap-2 mt-1">
                                    @if($project->project_url)<a href="{{ $project->project_url }}" target="_blank" class="small">Project</a>@endif
                                    @if($project->repository_url)<a href="{{ $project->repository_url }}" target="_blank" class="small">Repository</a>@endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($user->certifications->isNotEmpty())
                    <div class="section-block">
                        <div class="section-title">الشهادات</div>
                        @foreach($user->certifications as $certification)
                            <div class="mb-2">
                                <div class="fw-bold">{{ $certification->name }}</div>
                                <div class="text-muted small">{{ $certification->issuing_organization }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="section-block">
                    <div class="section-title">قوة الملف</div>
                    <div class="strength">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold">{{ ucfirst($completion['strength'] ?? 'starter') }}</span>
                            <span class="text-primary fw-bold">{{ $score }}%</span>
                        </div>
                        <div class="progress" style="height:7px">
                            <div class="progress-bar" style="width: {{ $score }}%"></div>
                        </div>
                        @foreach(array_slice($completion['suggestions'] ?? [], 0, 3) as $suggestion)
                            <div class="small text-muted mt-2">{{ $suggestion['message'] }}</div>
                        @endforeach
                    </div>
                </div>

                <div class="section-block">
                    <div class="section-title">معلومات وروابط</div>
                    <div class="small text-muted mb-2"><i class="fas fa-envelope me-1"></i>{{ $user->email }}</div>
                    @if($user->phone)<div class="small text-muted mb-2"><i class="fas fa-phone me-1"></i>{{ $user->phone }}</div>@endif
                    @if($profile?->location_city)<div class="small text-muted mb-2"><i class="fas fa-location-dot me-1"></i>{{ $profile->location_city }}</div>@endif
                    <div class="d-flex gap-2 flex-wrap mt-3">
                        @if($user->linkedin_url)<a href="{{ $user->linkedin_url }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">LinkedIn</a>@endif
                        @if($user->github_url)<a href="{{ $user->github_url }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill">GitHub</a>@endif
                        @if($user->portfolio_url)<a href="{{ $user->portfolio_url }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill">Portfolio</a>@endif
                    </div>
                </div>

                @if($user->languages->isNotEmpty())
                    <div class="section-block">
                        <div class="section-title">اللغات</div>
                        @foreach($user->languages as $language)
                            <div class="d-flex justify-content-between small mb-2">
                                <span>{{ $language->native_name ?: $language->name }}</span>
                                <span class="text-muted">{{ $language->pivot?->proficiency_level }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
