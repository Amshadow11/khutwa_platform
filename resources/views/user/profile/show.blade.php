@extends('layouts.app')
@section('title', 'ملفي الشخصي')

@push('styles')
<style>
    .profile-page { padding: 1.5rem 0 3rem; }
    .profile-cover {
        background: linear-gradient(135deg, #2C5AA0, #1e4085);
        border-radius: 16px 16px 0 0;
        height: 120px; position: relative;
    }
    .profile-avatar {
        width: 90px; height: 90px; border-radius: 50%;
        border: 4px solid #fff; object-fit: cover;
        position: absolute; bottom: -45px; right: 1.5rem;
        background: #e9ecef;
    }
    .profile-card {
        border: none; border-radius: 16px;
        box-shadow: 0 2px 14px rgba(0,0,0,.07);
        overflow: hidden;
    }
    .profile-info { padding: 3.5rem 1.5rem 1.5rem; }
    .info-row {
        display: flex; align-items: center; gap: .75rem;
        padding: .6rem 0; border-bottom: 1px solid #f5f5f5;
        font-size: .88rem;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row i { width: 18px; color: #2C5AA0; }
    .skill-tag {
        display: inline-block; background: rgba(44,90,160,.08);
        color: #2C5AA0; border-radius: 20px; padding: .25rem .75rem;
        font-size: .78rem; margin: .2rem;
    }
    .section-title {
        font-weight: 700; font-size: .82rem; color: #888;
        text-transform: uppercase; letter-spacing: .05em;
        margin-bottom: .75rem; margin-top: 1.25rem;
    }
</style>
@endpush

@section('content')
<div class="profile-page">
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-8">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3"
             style="border-radius:10px;border:none">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="profile-card card mb-3">
        {{-- Cover --}}
        <div class="profile-cover">
            <img src="{{ $user->avatar_url }}" class="profile-avatar" alt="">
        </div>

        {{-- Info --}}
        <div class="profile-info">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <h4 class="fw-bold mb-0">{{ $user->display_name }}</h4>
                    <div class="text-muted small">{{ '@' . $user->username }}</div>
                </div>
                <a href="{{ route('user.profile.edit') }}"
                   class="btn btn-outline-primary rounded-pill px-4 btn-sm">
                    <i class="fas fa-edit me-1"></i>تعديل الملف
                </a>
            </div>

            @if($user->bio)
                <p class="text-muted" style="font-size:.9rem;line-height:1.7">{{ $user->bio }}</p>
            @endif

            {{-- روابط مهنية --}}
            @if($user->linkedin_url || $user->github_url || $user->portfolio_url)
            <div class="d-flex gap-2 flex-wrap mb-3">
                @if($user->linkedin_url)
                    <a href="{{ $user->linkedin_url }}" target="_blank"
                       class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="fab fa-linkedin me-1"></i>LinkedIn
                    </a>
                @endif
                @if($user->github_url)
                    <a href="{{ $user->github_url }}" target="_blank"
                       class="btn btn-sm btn-outline-dark rounded-pill px-3">
                        <i class="fab fa-github me-1"></i>GitHub
                    </a>
                @endif
                @if($user->portfolio_url)
                    <a href="{{ $user->portfolio_url }}" target="_blank"
                       class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-globe me-1"></i>Portfolio
                    </a>
                @endif
            </div>
            @endif

            {{-- معلومات الاتصال --}}
            <div class="section-title">معلومات الاتصال</div>
            <div class="info-row"><i class="fas fa-envelope"></i> {{ $user->email }}</div>
            @if($user->phone)
                <div class="info-row"><i class="fas fa-phone"></i> {{ $user->phone }}</div>
            @endif
            @if($user->address)
                <div class="info-row"><i class="fas fa-map-marker-alt"></i> {{ $user->address }}</div>
            @endif
            @if($user->birth_date)
                <div class="info-row">
                    <i class="fas fa-birthday-cake"></i>
                    {{ $user->birth_date->format('Y/m/d') }}
                    ({{ $user->birth_date->age }} سنة)
                </div>
            @endif

            {{-- المهارات --}}
            @if($user->skills)
                <div class="section-title">المهارات</div>
                <div class="mb-2">
                    @foreach(explode(',', $user->skills) as $skill)
                        @if(trim($skill))
                            <span class="skill-tag">{{ trim($skill) }}</span>
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- الخبرة والتعليم --}}
            @if($user->experience)
                <div class="section-title">الخبرة</div>
                <p class="text-muted" style="font-size:.88rem;white-space:pre-wrap">{{ $user->experience }}</p>
            @endif
            @if($user->education)
                <div class="section-title">التعليم</div>
                <p class="text-muted" style="font-size:.88rem;white-space:pre-wrap">{{ $user->education }}</p>
            @endif

            {{-- نسبة اكتمال الملف --}}
            @php
                $fields  = ['profile_picture','bio','phone','address','skills','experience','education'];
                $filled  = collect($fields)->filter(fn($f) => !empty($user->$f))->count();
                $percent = intval(($filled / count($fields)) * 100);
            @endphp
            <div class="mt-3 p-3 rounded-3" style="background:#f8f9fa">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="fw-semibold">اكتمال الملف الشخصي</small>
                    <small class="text-primary fw-bold">{{ $percent }}%</small>
                </div>
                <div class="progress" style="height:6px;border-radius:10px">
                    <div class="progress-bar bg-primary" style="width: ( percent) ; border-radius: 10px"></div>
                </div>
                @if($percent < 100)
                    <small class="text-muted mt-1 d-block">
                        الملف الكامل يزيد فرصك في القبول —
                        <a href="{{ route('user.profile.edit') }}" class="text-primary">أكمل ملفك</a>
                    </small>
                @endif
            </div>

        </div>
    </div>

    {{-- إجراءات سريعة --}}
    <div class="row g-2">
        <div class="col-6">
            <a href="{{ route('user.applications.index') }}"
               class="card text-center p-3 text-decoration-none"
               style="border:none;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.05)">
                <i class="fas fa-paper-plane fa-2x text-primary mb-2"></i>
                <div class="small fw-semibold">طلباتي</div>
            </a>
        </div>
        <div class="col-6">
            <a href="{{ route('user.profile.edit') }}"
               class="card text-center p-3 text-decoration-none"
               style="border:none;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.05)">
                <i class="fas fa-edit fa-2x text-primary mb-2"></i>
                <div class="small fw-semibold">تعديل الملف</div>
            </a>
        </div>
    </div>

</div>
</div>
</div>
</div>
@endsection
