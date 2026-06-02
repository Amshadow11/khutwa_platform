@extends('layouts.app')
@section('title', $user->display_name)
@section('description', $description)

@push('styles')
<link rel="canonical" href="{{ $canonicalUrl }}">
<meta property="og:type" content="profile">
<meta property="og:title" content="{{ $user->display_name }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => $user->display_name,
    'url' => $canonicalUrl,
    'jobTitle' => $profile->current_title,
    'worksFor' => $profile->current_company ? ['@type' => 'Organization', 'name' => $profile->current_company] : null,
    'sameAs' => array_values(array_filter([$user->linkedin_url, $user->github_url, $user->portfolio_url])),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<style>
    .public-profile { padding: 2rem 0 3rem; }
    .public-shell { max-width: 960px; margin: 0 auto; }
    .hero { background: #fff; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 14px rgba(0,0,0,.06); }
    .avatar { width: 92px; height: 92px; border-radius: 50%; object-fit: cover; border: 3px solid #e9ecef; }
    .block { background: #fff; border-radius: 12px; padding: 1.25rem; box-shadow: 0 2px 12px rgba(0,0,0,.05); margin-top: 1rem; }
    .block-title { color: #2C5AA0; font-weight: 800; margin-bottom: .8rem; }
    .skill { display:inline-block; background:rgba(44,90,160,.08); color:#2C5AA0; padding:.3rem .75rem; border-radius:999px; margin:.15rem; font-size:.85rem; }
</style>
@endpush

@section('content')
<div class="public-profile">
    <div class="container public-shell">
        <div class="hero d-flex align-items-center gap-3 flex-wrap">
            <img src="{{ $user->avatar_url }}" class="avatar" alt="{{ $user->display_name }}">
            <div>
                <h1 class="h3 fw-bold mb-1">{{ $user->display_name }}</h1>
                @if($profile->headline)<div class="fw-semibold">{{ $profile->headline }}</div>@endif
                <div class="text-muted small">
                    {{ $profile->current_title }}
                    @if($profile->current_title && $profile->current_company) · @endif
                    {{ $profile->current_company }}
                    @if($profile->location_city) · {{ $profile->location_city }} @endif
                </div>
                @if($profile->open_to_work)<span class="badge text-bg-success mt-2">Open to work</span>@endif
            </div>
        </div>

        @if($profile->isSectionPublic('about') && $user->bio)
            <div class="block">
                <div class="block-title">نبذة</div>
                <p class="mb-0 text-muted" style="line-height:1.8">{{ $user->bio }}</p>
            </div>
        @endif

        @if($profile->isSectionPublic('skills') && $user->canonicalSkills->isNotEmpty())
            <div class="block">
                <div class="block-title">المهارات</div>
                @foreach($user->canonicalSkills as $skill)
                    <span class="skill">{{ $skill->name }}</span>
                @endforeach
            </div>
        @endif

        @if($profile->isSectionPublic('experience') && $user->experiences->isNotEmpty())
            <div class="block">
                <div class="block-title">الخبرات</div>
                @foreach($user->experiences as $experience)
                    <div class="mb-3">
                        <div class="fw-bold">{{ $experience->title }}</div>
                        <div class="text-muted small">{{ $experience->company_name }} {{ $experience->is_current ? '· حاليًا' : '' }}</div>
                        @if($experience->summary)<div class="text-muted mt-1">{{ $experience->summary }}</div>@endif
                    </div>
                @endforeach
            </div>
        @endif

        @if($profile->isSectionPublic('education') && $user->educations->isNotEmpty())
            <div class="block">
                <div class="block-title">التعليم</div>
                @foreach($user->educations as $education)
                    <div class="mb-2">
                        <div class="fw-bold">{{ $education->institution_name }}</div>
                        <div class="text-muted small">{{ $education->degree }} {{ $education->field_of_study ? '· '.$education->field_of_study : '' }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($profile->isSectionPublic('projects') && $user->projects->isNotEmpty())
            <div class="block">
                <div class="block-title">المشاريع</div>
                @foreach($user->projects as $project)
                    <div class="mb-2">
                        <div class="fw-bold">{{ $project->title }}</div>
                        @if($project->description)<div class="text-muted">{{ $project->description }}</div>@endif
                    </div>
                @endforeach
            </div>
        @endif

        @if($profile->isSectionPublic('certifications') && $user->certifications->isNotEmpty())
            <div class="block">
                <div class="block-title">الشهادات</div>
                @foreach($user->certifications as $certification)
                    <div class="mb-2">
                        <div class="fw-bold">{{ $certification->name }}</div>
                        <div class="text-muted small">{{ $certification->issuing_organization }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($profile->isSectionPublic('languages') && $user->languages->isNotEmpty())
            <div class="block">
                <div class="block-title">اللغات</div>
                @foreach($user->languages as $language)
                    <span class="skill">{{ $language->native_name ?: $language->name }} · {{ $language->pivot?->proficiency_level }}</span>
                @endforeach
            </div>
        @endif

        @if($profile->isSectionPublic('links') && ($user->linkedin_url || $user->github_url || $user->portfolio_url))
            <div class="block">
                <div class="block-title">روابط</div>
                <div class="d-flex gap-2 flex-wrap">
                    @if($user->linkedin_url)<a href="{{ $user->linkedin_url }}" target="_blank" rel="nofollow noopener" class="btn btn-sm btn-outline-primary rounded-pill">LinkedIn</a>@endif
                    @if($user->github_url)<a href="{{ $user->github_url }}" target="_blank" rel="nofollow noopener" class="btn btn-sm btn-outline-dark rounded-pill">GitHub</a>@endif
                    @if($user->portfolio_url)<a href="{{ $user->portfolio_url }}" target="_blank" rel="nofollow noopener" class="btn btn-sm btn-outline-secondary rounded-pill">Portfolio</a>@endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
