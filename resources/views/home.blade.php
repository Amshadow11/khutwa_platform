@extends('layouts.app')
@section('title', 'منصة خطوة — التوظيف الأول في اليمن')

@push('styles')
<style>
.hero {
    background: linear-gradient(135deg, #1a2942 0%, #2C5AA0 60%, #1e4085 100%);
    position: relative; overflow: hidden;
    padding: 5rem 0 4rem; color: #fff;
}
.hero::before {
    content:'';position:absolute;inset:0;
    background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
}
.hero-title { font-size:3rem; font-weight:800; line-height:1.2; margin-bottom:1rem; }
.hero-title span { color:#F8B500; }
.hero-subtitle { font-size:1.1rem; opacity:.85; margin-bottom:2rem; line-height:1.7; }

.search-card {
    background:rgba(255,255,255,.12); backdrop-filter:blur(10px);
    border:1px solid rgba(255,255,255,.2); border-radius:16px; padding:1.25rem;
}
.search-card .form-control,
.search-card .form-select {
    border-radius:10px; border:none; height:48px;
    font-size:.9rem; font-family:inherit;
}
.search-card .form-control:focus,
.search-card .form-select:focus { box-shadow:0 0 0 3px rgba(248,181,0,.4); }
.btn-search {
    height:48px; background:#F8B500; border:none; border-radius:10px;
    color:#1a2942; font-weight:700; font-size:.95rem; font-family:inherit;
    width:100%; transition:all .2s; padding:0 1.5rem;
}
.btn-search:hover { background:#e5a800; transform:translateY(-1px); }

.stats-bar {
    background:rgba(255,255,255,.1); border-radius:12px;
    padding:1rem 1.5rem; margin-top:2rem;
    display:flex; gap:2rem; flex-wrap:wrap;
}
.stat-item .num { font-size:1.6rem; font-weight:800; color:#F8B500; line-height:1; }
.stat-item .lbl { font-size:.75rem; opacity:.7; margin-top:.2rem; }

.section-title { font-size:1.6rem; font-weight:800; color:#1a2942; }

/* Job Card */
.job-card {
    background:#fff; border-radius:14px; padding:1.25rem;
    box-shadow:0 2px 12px rgba(0,0,0,.06); transition:all .25s;
    height:100%; display:flex; flex-direction:column;
    text-decoration:none; color:inherit;
    border:1.5px solid transparent;
}
.job-card:hover {
    transform:translateY(-3px); color:inherit;
    box-shadow:0 8px 25px rgba(44,90,160,.15);
    border-color:rgba(44,90,160,.2);
}
.job-card .co-logo {
    width:46px; height:46px; border-radius:10px;
    object-fit:cover; background:#f0f2f5; flex-shrink:0;
}
.job-card .job-title { font-weight:700; font-size:.95rem; color:#1a2942; line-height:1.3; }
.job-card .co-name   { font-size:.78rem; color:#888; }
.job-card .job-meta  { display:flex; gap:.5rem; flex-wrap:wrap; margin-top:auto; padding-top:.75rem; }
.meta-tag    { background:#f4f6fb; border-radius:6px; padding:.25rem .6rem; font-size:.72rem; color:#666; }
.badge-urgent{ background:#fff5f5; color:#dc3545; border:1px solid #fcc; border-radius:6px; padding:.25rem .6rem; font-size:.72rem; }
.badge-remote{ background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; border-radius:6px; padding:.25rem .6rem; font-size:.72rem; }

/* Company Card */
.company-card {
    background:#fff; border-radius:14px; padding:1.5rem 1.25rem;
    box-shadow:0 2px 12px rgba(0,0,0,.06); text-align:center;
    transition:all .25s; text-decoration:none; color:inherit;
    display:block; border:1.5px solid transparent;
}
.company-card:hover { transform:translateY(-3px); color:inherit; border-color:rgba(44,90,160,.2); }
.company-card .logo { width:60px; height:60px; border-radius:14px; object-fit:cover; margin:0 auto .75rem; background:#f0f2f5; }
.company-card .name { font-weight:700; font-size:.92rem; color:#1a2942; }
.company-card .ind  { font-size:.75rem; color:#888; margin-top:.2rem; }
.company-card .jcount {
    display:inline-block; margin-top:.5rem;
    background:rgba(44,90,160,.08); color:#2C5AA0;
    border-radius:20px; padding:.2rem .7rem;
    font-size:.72rem; font-weight:600;
}

.cta-section {
    background:linear-gradient(135deg,#1a2942,#2C5AA0);
    border-radius:20px; padding:3rem 2rem;
    text-align:center; color:#fff;
}

@media(max-width:768px){
    .hero { padding:3rem 0 2.5rem; }
    .hero-title { font-size:1.85rem; }
    .stats-bar  { gap:1rem; justify-content:center; }
    .stat-item .num { font-size:1.3rem; }
}
</style>
@endpush

@section('content')

{{-- ======= Hero ======= --}}
<section class="hero">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="hero-title">
                    اعثر على <span>وظيفتك المثالية</span><br>في اليمن
                </h1>
                <p class="hero-subtitle">
                    منصة خطوة تربط أصحاب العمل بالكفاءات اليمنية.<br>
                    آلاف الوظائف في مختلف القطاعات تنتظرك.
                </p>

                <div class="search-card">
                    <form action="{{ route('search') }}" method="GET">
                        <div class="row g-2">
                            <div class="col-12 col-md-5">
                                <div class="position-relative">
                                    <i class="fas fa-search position-absolute"
                                       style="right:13px;top:50%;transform:translateY(-50%);color:#aaa;z-index:5;font-size:.85rem"></i>
                                    <input type="text" name="keyword"
                                           class="form-control pe-5"
                                           placeholder="المسمى الوظيفي أو الشركة..."
                                           value="{{ request('keyword') }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="position-relative">
                                    <i class="fas fa-map-marker-alt position-absolute"
                                       style="right:13px;top:50%;transform:translateY(-50%);color:#aaa;z-index:5;font-size:.85rem"></i>
                                    <select name="location" class="form-select pe-5">
                                        <option value="">كل المدن</option>
                                        @foreach(['صنعاء','عدن','تعز','الحديدة','إب','ذمار','المكلا','مأرب'] as $city)
                                            <option {{ request('location')===$city?'selected':'' }}>{{ $city }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <button type="submit" class="btn-search">
                                    <i class="fas fa-search me-1"></i> ابحث
                                </button>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap mt-2 align-items-center">
                            <span style="font-size:.76rem;opacity:.6">الأكثر بحثاً:</span>
                            @foreach(['مبرمج','محاسب','مهندس','مصمم','تسويق'] as $t)
                                <a href="{{ route('search', ['keyword' => $t, 'location' => request('location')]) }}"
                                   style="font-size:.76rem;color:rgba(255,255,255,.8);
                                      text-decoration:none;background:rgba(255,255,255,.1);
                                      padding:2px 9px;border-radius:20px">{{ $t }}</a>
                            @endforeach
                        </div>
                    </form>
                </div>

                <div class="stats-bar">
                    @foreach([
                        [$stats['users'],        'باحث عمل'],
                        [$stats['companies'],     'شركة موثّقة'],
                        [$stats['jobs'],          'وظيفة متاحة'],
                        [$stats['applications'],  'طلب توظيف'],
                    ] as [$n, $l])
                    <div class="stat-item">
                        <div class="num">{{ number_format($n) }}+</div>
                        <div class="lbl">{{ $l }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-4 d-none d-lg-flex justify-content-center">
                <div style="width:220px;height:220px;background:rgba(255,255,255,.06);
                            border-radius:50%;display:flex;align-items:center;justify-content:center">
                    <i class="fas fa-shoe-prints"
                       style="font-size:6rem;opacity:.25;transform:rotate(-20deg)"></i>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ======= أحدث الوظائف ======= --}}
<section class="py-5" style="background:#f4f6fb">
    <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <h2 class="section-title mb-1">أحدث الوظائف</h2>
                <p class="text-muted small mb-0">وظائف جديدة تُضاف يومياً</p>
            </div>
            <a href="{{ route('jobs.index') }}" class="btn btn-outline-primary rounded-pill px-4">
                عرض الكل <i class="fas fa-arrow-left me-1"></i>
            </a>
        </div>

        @if($latestJobs->isEmpty())
            <div class="text-center py-4 text-muted">
                <i class="fas fa-briefcase fa-3x mb-3 d-block" style="opacity:.2"></i>
                لا توجد وظائف متاحة حالياً
            </div>
        @else
        <div class="row g-3">
            @foreach($latestJobs as $job)
            <div class="col-12 col-md-6 col-lg-4">
                <a href="{{ route('jobs.show', $job) }}" class="job-card">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <img src="{{ $job->company->logo_url ?? asset('images/default-company.png') }}"
                             class="co-logo" alt="">
                        <div class="overflow-hidden">
                            <div class="job-title">{{ $job->title }}</div>
                            <div class="co-name">{{ $job->company->company_name ?? '' }}</div>
                        </div>
                    </div>
                    @if($job->location)
                    <div class="text-muted mb-2" style="font-size:.8rem">
                        <i class="fas fa-map-marker-alt me-1 text-primary"></i>{{ $job->location }}
                        @if($job->salary)
                            &ensp;<i class="fas fa-money-bill-wave me-1 text-success"></i>{{ $job->salary }}
                        @endif
                    </div>
                    @endif
                    <div class="job-meta">
                        @if($job->urgent) <span class="badge-urgent"><i class="fas fa-bolt me-1"></i>عاجل</span> @endif
                        @if($job->remote_work) <span class="badge-remote"><i class="fas fa-wifi me-1"></i>بُعد</span> @endif
                        <span class="meta-tag">{{ $job->job_type_label }}</span>
                        <span class="text-muted ms-auto" style="font-size:.72rem">
                            {{ $job->created_at->diffForHumans() }}
                        </span>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('jobs.index') }}" class="btn btn-primary rounded-pill px-5">
                <i class="fas fa-search me-2"></i>استعرض كل الوظائف
            </a>
        </div>
        @endif
    </div>
</section>

{{-- ======= الشركات المميزة ======= --}}
<section class="py-5" style="background:#fff">
    <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <h2 class="section-title mb-1">الشركات المميزة</h2>
                <p class="text-muted small mb-0">أبرز أصحاب العمل في اليمن</p>
            </div>
            <a href="#" class="btn btn-outline-primary rounded-pill px-4">
                كل الشركات <i class="fas fa-arrow-left me-1"></i>
            </a>
        </div>

        @if($featuredCompanies->isNotEmpty())
        <div class="row g-3">
            @foreach($featuredCompanies as $company)
            <div class="col-6 col-md-4 col-lg-2">
                <a href="#" class="company-card">
                    <img src="{{ $company->logo_url }}" class="logo" alt="{{ $company->company_name }}">
                    <div class="name">{{ Str::limit($company->company_name, 20) }}</div>
                    @if($company->industry)
                        <div class="ind">{{ $company->industry }}</div>
                    @endif
                    <span class="jcount">
                        {{ $company->jobs_count }} وظيفة
                    </span>
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>

{{-- ======= كيف تعمل المنصة ======= --}}
<section class="py-5" style="background:#f4f6fb">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="section-title">كيف تعمل منصة خطوة؟</h2>
            <p class="text-muted small">ثلاث خطوات بسيطة للوصول إلى وظيفتك</p>
        </div>
        <div class="row g-4 text-center">
            @foreach([
                ['user-plus',   '1', 'سجّل حسابك',      'أنشئ ملفك الشخصي ورفع سيرتك الذاتية في دقيقتين'],
                ['search',      '2', 'ابحث وتصفّح',      'استعرض آلاف الوظائف وفلترها حسب التخصص والمدينة'],
                ['paper-plane', '3', 'قدّم واحصل على العمل','تقدّم بنقرة واحدة وتابع حالة طلبك لحظة بلحظة'],
            ] as [$icon, $num, $title, $desc])
            <div class="col-md-4">
                <div class="card h-100 py-4 position-relative">
                    <div class="card-body text-center">
                        <div style="width:70px;height:70px;background:linear-gradient(135deg,#2C5AA0,#1e4085);
                                    border-radius:50%;display:flex;align-items:center;justify-content:center;
                                    margin:0 auto 1rem;font-size:1.5rem;color:#fff">
                            <i class="fas fa-{{ $icon }}"></i>
                        </div>
                        <span style="position:absolute;top:1rem;left:1rem;width:26px;height:26px;
                                     background:#F8B500;border-radius:50%;display:flex;
                                     align-items:center;justify-content:center;
                                     font-size:.78rem;font-weight:800;color:#1a2942">
                            {{ $num }}
                        </span>
                        <h5 class="fw-bold mb-2">{{ $title }}</h5>
                        <p class="text-muted small mb-0" style="line-height:1.7">{{ $desc }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ======= CTA ======= --}}
<section class="py-5" style="background:#fff">
    <div class="container">
        <div class="cta-section">
            <h2 class="fw-bold mb-2" style="font-size:1.8rem">هل أنت صاحب عمل؟</h2>
            <p style="opacity:.8;margin-bottom:2rem">
                انضم لمئات الشركات اليمنية وابدأ التوظيف اليوم مجاناً
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="{{ route('register') }}"
                   class="btn btn-warning rounded-pill px-5 fw-bold"
                   style="color:#1a2942;height:48px;line-height:48px;padding-top:0;padding-bottom:0">
                    <i class="fas fa-building me-2"></i>سجّل شركتك مجاناً
                </a>
                <a href="{{ route('login') }}"
                   class="btn btn-outline-light rounded-pill px-5"
                   style="height:48px;line-height:46px;padding-top:0;padding-bottom:0">
                    <i class="fas fa-sign-in-alt me-2"></i>تسجيل الدخول
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
