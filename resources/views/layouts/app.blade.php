<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'منصة خطوة') — منصة التوظيف الأولى في اليمن</title>
    <meta name="description" content="@yield('description', 'ابحث عن وظيفتك المناسبة أو وظّف أفضل المواهب اليمنية')">

    {{-- Bootstrap RTL --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- خط Tajawal العربي (Bunny Fonts — أسرع من Google في اليمن) --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=tajawal:300,400,500,700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary:     #2C5AA0;
            --primary-dark:#1e4085;
            --secondary:   #F8B500;
            --success:     #28a745;
            --danger:      #dc3545;
            --warning:     #ffc107;
            --info:        #17a2b8;
            --light:       #f8f9fa;
            --dark:        #343a40;
            --gradient:    linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            --sidebar-w:   250px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Tajawal', 'Segoe UI', Tahoma, sans-serif;
            background-color: #f4f6fb;
            color: #333;
            font-size: 15px;
        }

        /* ---- Navbar ---- */
        .navbar-khutwa {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
            padding: .75rem 0;
        }
        .navbar-brand {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary) !important;
        }
        .nav-link {
            color: #555 !important;
            font-weight: 500;
            padding: .5rem .9rem !important;
            border-radius: 8px;
            transition: all .2s;
        }
        .nav-link:hover, .nav-link.active {
            background: rgba(44,90,160,.08);
            color: var(--primary) !important;
        }

        /* ---- Alerts ---- */
        .alert { border-radius: 10px; border: none; }

        /* ---- Cards ---- */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
        }
        .card-header {
            background: transparent;
            border-bottom: 1px solid #f0f0f0;
            font-weight: 700;
            padding: 1rem 1.25rem;
        }

        /* ---- Buttons ---- */
        .btn-primary {
            background: var(--gradient);
            border: none;
            border-radius: 8px;
        }
        .btn-primary:hover { opacity: .9; transform: translateY(-1px); }

        /* ---- Bottom Nav Mobile ---- */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: #fff;
            border-top: 1px solid #eee;
            z-index: 1040;
            padding: 6px 0 8px;
        }
        .mobile-bottom-nav a,
        .mobile-bottom-nav form {
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
        .mobile-bottom-nav form button {
            border: none;
            background: none;
            color: #999;
            font-size: 11px;
            padding: 0;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
        }
        .mobile-bottom-nav form button:hover {
            color: var(--primary);
        }
        .mobile-bottom-nav a i { font-size: 1.2rem; }
        .mobile-bottom-nav a.active { color: var(--primary); }

        @media (max-width: 768px) {
            .mobile-bottom-nav { display: flex; }
            body { padding-bottom: 65px; }
            .navbar-khutwa { display: none; } /* نستخدم bottom nav بدلاً منه */
        }

        /* ---- Responsiveness ---- */
        @media (max-width: 576px) {
            .card { border-radius: 8px; }
            h1 { font-size: 1.5rem; }
            h2 { font-size: 1.3rem; }
        }
    </style>

    {{-- PWA --}}
    <link rel="manifest" href="/site.manifest.json">    
    <meta name="theme-color" content="#2C5AA0">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="خطوة">
    @vite([ 'resources/js/app.js', 'resources/css/app.css' ])

    @stack('styles')
</head>
<body>

{{-- ===== Navbar (Desktop) ===== --}}
<nav class="navbar navbar-expand-lg navbar-khutwa sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <i class="fas fa-shoe-prints me-1"></i>منصة خطوة
        </a>

        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto gap-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                       href="{{ route('home') }}">
                        <i class="fas fa-home me-1"></i>الرئيسية
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('jobs.index') }}"><i class="fas fa-briefcase me-1"></i>الوظائف</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href=""><i class="fas fa-building me-1"></i>الشركات</a>
                </li>
            </ul>
             <ul class="navbar-nav gap-1">
                @auth('company')
                    @php $companyUser = auth('company')->user(); @endphp
                    {{-- الرسائل --}}
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="{{ route('messages.index') }}">
                            <i class="fas fa-comment"></i>
                            @if($companyUser->unread_messages_count > 0)
                                <span class="position-absolute top-0 start-0 badge rounded-pill bg-danger" style="font-size:.55rem;padding:2px 5px">
                                    {{ $companyUser->unread_messages_count }}
                                </span>
                            @endif
                        </a>
                    </li>
                    {{-- الإشعارات --}}
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="{{ route('notifications.index') }}">
                            <i class="fas fa-bell"></i>
                            @if($companyUser->unreadNotifications()->count() > 0)
                                <span class="position-absolute top-0 start-0 badge rounded-pill bg-danger" style="font-size:.55rem;padding:2px 5px">
                                    {{ $companyUser->unreadNotifications()->count() }}
                                </span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('company.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-1"></i>لوحة التحكم
                        </a>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                <i class="fas fa-sign-out-alt me-1"></i>خروج
                            </button>
                        </form>
                    </li>
                @elseauth('web')
                    @php $webUser = auth('web')->user(); @endphp
                    {{-- الرسائل --}}
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="{{ route('messages.index') }}">
                            <i class="fas fa-comment"></i>
                            @if($webUser->unread_messages_count > 0)
                                <span class="position-absolute top-0 start-0 badge rounded-pill bg-danger" style="font-size:.55rem;padding:2px 5px">
                                    {{ $webUser->unread_messages_count }}
                                </span>
                            @endif
                        </a>
                    </li>
                    {{-- الإشعارات --}}
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="{{ route('notifications.index') }}">
                            <i class="fas fa-bell"></i>
                            @if($webUser->unreadNotifications()->count() > 0)
                                <span class="position-absolute top-0 start-0 badge rounded-pill bg-danger" style="font-size:.55rem;padding:2px 5px">
                                    {{ $webUser->unreadNotifications()->count() }}
                                </span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('user.dashboard') }}">
                            <i class="fas fa-user me-1"></i>حسابي
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('user.profile.show') }}">
                            <i class="fas fa-user me-1"></i>>الملف الشخصي
                        </a>
                    </li>
     
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                <i class="fas fa-sign-out-alt me-1"></i>خروج
                            </button>
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt me-1"></i>تسجيل الدخول
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm px-3 rounded-pill"
                           href="{{ route('register') }}">
                            إنشاء حساب
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

{{-- ===== Flash Messages ===== --}}
@if(session('success'))
    <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="container mt-3">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
@endif

{{-- ===== Main Content ===== --}}
<main>
    @yield('content')
</main>

{{-- ===== Mobile Bottom Navigation ===== --}}
<nav class="mobile-bottom-nav">
    <a href="{{ route('home') }}"
       class="{{ request()->routeIs('home') ? 'active' : '' }}">
        <i class="fas fa-home"></i>
        <span>الرئيسية</span>
    </a>
    <a href="{{ route('jobs.index') }}"
       class="{{ request()->routeIs('jobs.*') ? 'active' : '' }}">
        <i class="fas fa-briefcase"></i>
        <span>الوظائف</span>
    </a>

    @auth('web')
        @php $webUser = auth('web')->user(); @endphp
        <a href="{{ route('messages.index') }}"
           class="{{ request()->routeIs('messages.*') ? 'active' : '' }}">
            <i class="fas fa-comment"></i>
            @if($webUser->unread_messages_count > 0)
                <span class="position-absolute" style="top:-2px;left:50%;transform:translateX(-50%);background:#dc3545;color:#fff;border-radius:10px;font-size:10px;padding:1px 4px;min-width:16px;text-align:center;font-weight:600;">
                    {{ $webUser->unread_messages_count > 99 ? '99+' : $webUser->unread_messages_count }}
                </span>
            @endif
            <span>الرسائل</span>
        </a>
        <a href="{{ route('notifications.index') }}"
           class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <i class="fas fa-bell"></i>
            @if($webUser->unreadNotifications()->count() > 0)
                <span class="position-absolute" style="top:-2px;left:50%;transform:translateX(-50%);background:#dc3545;color:#fff;border-radius:10px;font-size:10px;padding:1px 4px;min-width:16px;text-align:center;font-weight:600;">
                    {{ $webUser->unreadNotifications()->count() > 99 ? '99+' : $webUser->unreadNotifications()->count() }}
                </span>
            @endif
            <span>الإشعارات</span>
        </a>
        <a href="{{ route('user.profile.show') }}"
           class="{{ request()->routeIs('user.profile*') ? 'active' : '' }}">
            <i class="fas fa-user"></i>
            <span>الملف</span>
        </a>
        <form action="{{ route('logout') }}" method="POST" class="d-inline" style="flex:1;">
            @csrf
            <button type="submit" class="w-100 border-0 bg-transparent text-decoration-none d-flex flex-column align-items-center" style="color:#999;font-size:11px;gap:3px;padding:0;">
                <i class="fas fa-sign-out-alt" style="font-size:1.2rem;"></i>
                <span>خروج</span>
            </button>
        </form>
    @elseauth('company')
        @php $companyUser = auth('company')->user(); @endphp
        <a href="{{ route('messages.index') }}"
           class="{{ request()->routeIs('messages.*') ? 'active' : '' }}">
            <i class="fas fa-comment"></i>
            @if($companyUser->unread_messages_count > 0)
                <span class="position-absolute" style="top:-2px;left:50%;transform:translateX(-50%);background:#dc3545;color:#fff;border-radius:10px;font-size:10px;padding:1px 4px;min-width:16px;text-align:center;font-weight:600;">
                    {{ $companyUser->unread_messages_count > 99 ? '99+' : $companyUser->unread_messages_count }}
                </span>
            @endif
            <span>الرسائل</span>
        </a>
        <a href="{{ route('notifications.index') }}"
           class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <i class="fas fa-bell"></i>
            @if($companyUser->unreadNotifications()->count() > 0)
                <span class="position-absolute" style="top:-2px;left:50%;transform:translateX(-50%);background:#dc3545;color:#fff;border-radius:10px;font-size:10px;padding:1px 4px;min-width:16px;text-align:center;font-weight:600;">
                    {{ $companyUser->unreadNotifications()->count() > 99 ? '99+' : $companyUser->unreadNotifications()->count() }}
                </span>
            @endif
            <span>الإشعارات</span>
        </a>
        <a href="{{ route('company.dashboard') }}"
           class="{{ request()->routeIs('company.*') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i>
            <span>لوحتي</span>
        </a>
        <form action="{{ route('logout') }}" method="POST" class="d-inline" style="flex:1;">
            @csrf
            <button type="submit" class="w-100 border-0 bg-transparent text-decoration-none d-flex flex-column align-items-center" style="color:#999;font-size:11px;gap:3px;padding:0;">
                <i class="fas fa-sign-out-alt" style="font-size:1.2rem;"></i>
                <span>خروج</span>
            </button>
        </form>
    @else
        <a href="#">
            <i class="fas fa-building"></i>
            <span>الشركات</span>
        </a>
        <a href="{{ route('login') }}">
            <i class="fas fa-user"></i>
            <span>دخول</span>
        </a>
    @endauth
</nav>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

{{-- Service Worker Registration --}}
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(reg => console.log('SW registered:', reg.scope))
            .catch(err => console.log('SW error:', err));
    });
}
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {});
</script>
@stack('scripts')
</body>
</html>
