<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'لوحة التحكم') — {{ auth('company')->user()->company_name }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=tajawal:300,400,500,700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary:      #2C5AA0;
            --primary-dark: #1e4085;
            --secondary:    #F8B500;
            --sidebar-bg:   #1a2942;
            --sidebar-w:    240px;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background: #f0f2f7;
            margin: 0;
        }

        /* ======= Sidebar ======= */
        .sidebar {
            position: fixed;
            top: 0; right: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1030;
            overflow-y: auto;
            transition: transform .3s ease;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: .6rem;
        }
        .sidebar-brand i { color: var(--secondary); }

        .sidebar-company {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .sidebar-company img {
            width: 40px; height: 40px;
            border-radius: 8px;
            object-fit: cover;
            background: rgba(255,255,255,.1);
        }
        .sidebar-company .company-name {
            color: #fff;
            font-size: .9rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-company .company-status {
            font-size: .72rem;
            color: rgba(255,255,255,.5);
        }

        .sidebar-nav { padding: 1rem 0; }

        .sidebar-heading {
            color: rgba(255,255,255,.35);
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: .75rem 1.5rem .3rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .65rem 1.5rem;
            color: rgba(255,255,255,.7);
            text-decoration: none;
            font-size: .88rem;
            transition: all .2s;
            border-right: 3px solid transparent;
        }
        .sidebar-link i { width: 18px; text-align: center; opacity: .8; }
        .sidebar-link:hover {
            color: #fff;
            background: rgba(255,255,255,.06);
        }
        .sidebar-link.active {
            color: #fff;
            background: rgba(44,90,160,.4);
            border-right-color: var(--secondary);
        }
        .sidebar-link .badge-count {
            margin-right: auto;
            background: var(--secondary);
            color: #000;
            font-size: .68rem;
            padding: 2px 7px;
            border-radius: 10px;
        }

        /* ======= Main Content ======= */
        .main-wrapper {
            margin-right: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ======= Top Bar ======= */
        .topbar {
            background: #fff;
            padding: .75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 1px 6px rgba(0,0,0,.06);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .topbar .page-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #333;
            margin: 0;
        }
        .topbar .ms-auto { margin-right: auto !important; margin-left: 0 !important; }
        .topbar-actions { display: flex; align-items: center; gap: .75rem; }

        /* ======= Content Area ======= */
        .content-area {
            padding: 1.5rem;
            flex: 1;
        }

        /* ======= Cards ======= */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
        }
        .card-header {
            background: transparent;
            border-bottom: 1px solid #f0f0f0;
            font-weight: 700;
            padding: 1rem 1.25rem;
        }

        /* ======= Stat Cards ======= */
        .stat-card {
            border-radius: 12px;
            padding: 1.25rem;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .stat-card::after {
            content: '';
            position: absolute;
            top: -20px; left: -20px;
            width: 100px; height: 100px;
            border-radius: 50%;
            background: rgba(255,255,255,.08);
        }
        .stat-card .stat-icon {
            font-size: 2rem;
            opacity: .8;
            margin-bottom: .5rem;
        }
        .stat-card .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1;
        }
        .stat-card .stat-label {
            font-size: .82rem;
            opacity: .85;
            margin-top: .25rem;
        }
        .stat-blue   { background: linear-gradient(135deg, #2C5AA0, #1e4085); }
        .stat-green  { background: linear-gradient(135deg, #28a745, #1d7a35); }
        .stat-orange { background: linear-gradient(135deg, #F8B500, #e0a000); }
        .stat-purple { background: linear-gradient(135deg, #6f42c1, #553098); }

        /* ======= Tables ======= */
        .table { margin: 0; }
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            font-size: .82rem;
            color: #666;
            border-bottom: 2px solid #eee;
            white-space: nowrap;
        }
        .table td {
            vertical-align: middle;
            font-size: .88rem;
            border-bottom: 1px solid #f5f5f5;
        }

        /* ======= Status Badges ======= */
        .badge { font-size: .75rem; padding: .35em .7em; border-radius: 6px; }

        /* ======= Alerts ======= */
        .alert { border: none; border-radius: 10px; }

        /* ======= Mobile — Sidebar Toggle ======= */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 1029;
        }

        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(100%); /* مخفي خارج الشاشة من اليمين */
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .sidebar-overlay.show { display: block; }
            .main-wrapper { margin-right: 0; }
            .content-area { padding: 1rem; }

            /* Mobile Bottom Nav */
            .mobile-bottom-nav {
                display: flex;
                position: fixed;
                bottom: 0; left: 0; right: 0;
                background: #fff;
                border-top: 1px solid #eee;
                z-index: 1040;
                padding: 6px 0 8px;
            }
            body { padding-bottom: 65px; }
        }

        @media (min-width: 992px) {
            .mobile-bottom-nav { display: none !important; }
        }

        .mobile-bottom-nav a {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #999;
            font-size: 11px;
            gap: 3px;
            position: relative;
        }
        .mobile-bottom-nav a i { font-size: 1.2rem; }
        .mobile-bottom-nav a.active { color: var(--primary); }
    </style>

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2C5AA0">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="خطوة">

    @stack('styles')
</head>
<body>

{{-- ======= Sidebar ======= --}}
<aside class="sidebar" id="sidebar">

    {{-- Brand --}}
    <a class="sidebar-brand" href="{{ route('home') }}">
        <i class="fas fa-shoe-prints"></i>
        <span>منصة خطوة</span>
    </a>

    {{-- معلومات الشركة --}}
    <div class="sidebar-company">
        <img src="{{ auth('company')->user()->logo_url }}"
             alt="{{ auth('company')->user()->company_name }}">
        <div style="overflow:hidden">
            <div class="company-name">{{ auth('company')->user()->company_name }}</div>
            <div class="company-status">
                @if(auth('company')->user()->is_verified)
                    <i class="fas fa-check-circle text-success"></i> شركة موثّقة
                @else
                    <i class="fas fa-clock text-warning"></i> قيد المراجعة
                @endif
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav">

        <div class="sidebar-heading">الرئيسية</div>

        <a href="{{ route('company.dashboard') }}"
           class="sidebar-link {{ request()->routeIs('company.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i>
            <span>نظرة عامة</span>
        </a>

        <div class="sidebar-heading">الوظائف</div>

        <a href="{{ route('company.jobs.index') }}"
           class="sidebar-link {{ request()->routeIs('company.jobs.*') ? 'active' : '' }}">
            <i class="fas fa-briefcase"></i>
            <span>إدارة الوظائف</span>
        </a>

        <a href="{{ route('company.jobs.create') }}"
           class="sidebar-link {{ request()->routeIs('company.jobs.create') ? 'active' : '' }}">
            <i class="fas fa-plus-circle"></i>
            <span>نشر وظيفة جديدة</span>
        </a>

        <div class="sidebar-heading">المتقدمون</div>

        <a href="{{ route('company.applications.index') }}"
           class="sidebar-link {{ request()->routeIs('company.applications.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span>الطلبات الواردة</span>
            {{-- Badge للطلبات الجديدة — يُضاف لاحقاً مع Notifications --}}
        </a>

        <div class="sidebar-heading">التواصل</div>

        <a href="{{ route('messages.index') }}"
           class="sidebar-link {{ request()->routeIs('messages.*') ? 'active' : '' }}">
            <i class="fas fa-comment"></i>
            <span>الرسائل</span>
            @if(auth('company')->user()->unread_messages_count > 0)
                <span class="badge-count">{{ auth('company')->user()->unread_messages_count > 99 ? '99+' : auth('company')->user()->unread_messages_count }}</span>
            @endif
        </a>

        <a href="{{ route('notifications.index') }}"
           class="sidebar-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <i class="fas fa-bell"></i>
            <span>الإشعارات</span>
            @if(auth('company')->user()->unreadNotifications()->count() > 0)
                <span class="badge-count">{{ auth('company')->user()->unreadNotifications()->count() > 99 ? '99+' : auth('company')->user()->unreadNotifications()->count() }}</span>
            @endif
        </a>

        <div class="sidebar-heading">الحساب</div>

        <a href="#" class="sidebar-link">
            <i class="fas fa-building"></i>
            <span>ملف الشركة</span>
        </a>

        <a href="#" class="sidebar-link">
            <i class="fas fa-cog"></i>
            <span>الإعدادات</span>
        </a>

        <form action="{{ route('logout') }}" method="POST" class="mt-2">
            @csrf
            <button type="submit" class="sidebar-link w-100 text-start border-0 bg-transparent"
                    style="color:rgba(255,255,255,.5)">
                <i class="fas fa-sign-out-alt"></i>
                <span>تسجيل الخروج</span>
            </button>
        </form>
    </nav>
</aside>

{{-- Overlay للموبايل --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

{{-- ======= Main Wrapper ======= --}}
<div class="main-wrapper">

    {{-- Top Bar --}}
    <div class="topbar">
        {{-- زر فتح Sidebar في الموبايل --}}
        <button class="btn btn-sm btn-light d-lg-none" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <h1 class="page-title">@yield('page-title', 'لوحة التحكم')</h1>

        <div class="topbar-actions ms-auto">
            {{-- زر نشر وظيفة سريع --}}
            <a href="{{ route('company.jobs.create') }}"
               class="btn btn-primary btn-sm rounded-pill px-3 d-none d-md-inline-flex align-items-center gap-1">
                <i class="fas fa-plus"></i>
                <span>وظيفة جديدة</span>
            </a>

            {{-- اسم الشركة --}}
            <span class="text-muted small d-none d-md-block">
                {{ auth('company')->user()->company_name }}
            </span>
        </div>
    </div>

    {{-- Flash Messages --}}
    <div class="content-area pb-0">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>يوجد أخطاء في البيانات:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- Page Content --}}
    <div class="content-area">
        @yield('content')
    </div>

</div>

{{-- Mobile Bottom Nav --}}
<nav class="mobile-bottom-nav">
    <a href="{{ route('company.dashboard') }}"
       class="{{ request()->routeIs('company.dashboard') ? 'active' : '' }}">
        <i class="fas fa-th-large"></i><span>الرئيسية</span>
    </a>
    <a href="{{ route('company.jobs.index') }}"
       class="{{ request()->routeIs('company.jobs.*') ? 'active' : '' }}">
        <i class="fas fa-briefcase"></i><span>الوظائف</span>
    </a>
    <a href="{{ route('company.jobs.create') }}">
        <i class="fas fa-plus-circle" style="color:var(--primary);font-size:1.6rem;margin-top:-8px"></i>
        <span>نشر</span>
    </a>
    <a href="{{ route('company.applications.index') }}"
       class="{{ request()->routeIs('company.applications.*') ? 'active' : '' }}">
        <i class="fas fa-users"></i><span>الطلبات</span>
    </a>
    <a href="#">
        <i class="fas fa-building"></i><span>الملف</span>
    </a>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }
</script>

@stack('scripts')
</body>
</html>
