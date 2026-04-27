@extends('layouts.app')

@section('title', 'تسجيل الدخول')

@push('styles')
<style>
    body { background: linear-gradient(135deg, #2C5AA0 0%, #1e4085 100%); min-height: 100vh; }
    .navbar-khutwa { display: none; }

    .login-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .login-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,.25);
        width: 100%;
        max-width: 440px;
        padding: 2.5rem 2rem;
        animation: slideUp .4s ease;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .login-logo {
        text-align: center;
        margin-bottom: 1.5rem;
    }
    .login-logo .icon-wrap {
        width: 70px; height: 70px;
        background: linear-gradient(135deg, #2C5AA0, #1e4085);
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: #fff;
        margin-bottom: .75rem;
    }
    .login-logo h4 { font-weight: 700; color: #333; margin: 0; }
    .login-logo p  { color: #999; font-size: .85rem; margin: .25rem 0 0; }

    /* --- Type Tabs --- */
    .type-tabs {
        display: flex;
        background: #f4f6fb;
        border-radius: 10px;
        padding: 4px;
        margin-bottom: 1.5rem;
        gap: 4px;
    }
    .type-tab {
        flex: 1;
        padding: .55rem;
        text-align: center;
        border-radius: 8px;
        border: none;
        background: transparent;
        color: #888;
        font-size: .88rem;
        font-family: inherit;
        font-weight: 500;
        cursor: pointer;
        transition: all .25s;
    }
    .type-tab.active {
        background: #fff;
        color: #2C5AA0;
        box-shadow: 0 2px 8px rgba(0,0,0,.1);
        font-weight: 700;
    }

    /* --- Form Fields --- */
    .field-wrap {
        position: relative;
        margin-bottom: 1rem;
    }
    .field-icon {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #bbb;
        font-size: .9rem;
        z-index: 5;
    }
    .form-control {
        padding-right: 38px;
        border-radius: 10px;
        border: 1.5px solid #e5e7eb;
        height: 46px;
        font-size: .9rem;
        transition: border-color .2s;
        font-family: inherit;
    }
    .form-control:focus {
        border-color: #2C5AA0;
        box-shadow: 0 0 0 3px rgba(44,90,160,.12);
    }
    .form-control.is-invalid { border-color: #dc3545; }

    /* --- Submit Button --- */
    .btn-login {
        width: 100%;
        height: 48px;
        background: linear-gradient(135deg, #2C5AA0, #1e4085);
        border: none;
        border-radius: 10px;
        color: #fff;
        font-size: 1rem;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        transition: all .25s;
        margin-top: .5rem;
    }
    .btn-login:hover { opacity: .92; transform: translateY(-1px); }
    .btn-login:active { transform: translateY(0); }

    .divider {
        text-align: center;
        color: #bbb;
        font-size: .82rem;
        margin: 1.25rem 0;
        position: relative;
    }
    .divider::before, .divider::after {
        content: '';
        position: absolute;
        top: 50%;
        width: 38%;
        height: 1px;
        background: #eee;
    }
    .divider::before { right: 0; }
    .divider::after  { left: 0; }

    .register-link {
        text-align: center;
        font-size: .88rem;
        color: #777;
    }
    .register-link a { color: #2C5AA0; font-weight: 600; text-decoration: none; }
    .register-link a:hover { text-decoration: underline; }
</style>
@endpush

@section('content')
<div class="login-wrapper">
    <div class="login-card">

        {{-- Logo --}}
        <div class="login-logo">
            <div class="icon-wrap"><i class="fas fa-shoe-prints"></i></div>
            <h4>منصة خطوة</h4>
            <p>منصة التوظيف الأولى في اليمن</p>
        </div>

        {{-- Errors --}}
        @if($errors->any())
            <div class="alert alert-danger py-2 mb-3" style="border-radius:10px; border:none; font-size:.88rem">
                <i class="fas fa-exclamation-circle me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Login Form --}}
        <form action="{{ route('login.submit') }}" method="POST" id="loginForm">
            @csrf

            {{-- نوع الحساب --}}
            <div class="type-tabs" role="tablist">
                <button type="button"
                        class="type-tab {{ old('login_type', 'user') === 'user' ? 'active' : '' }}"
                        onclick="setType('user')">
                    <i class="fas fa-user me-1"></i>باحث عمل
                </button>
                <button type="button"
                        class="type-tab {{ old('login_type') === 'company' ? 'active' : '' }}"
                        onclick="setType('company')">
                    <i class="fas fa-building me-1"></i>شركة
                </button>
            </div>
            <input type="hidden" name="login_type" id="loginType"
                   value="{{ old('login_type', 'user') }}">

            {{-- البريد الإلكتروني --}}
            <div class="field-wrap">
                <i class="fas fa-envelope field-icon"></i>
                <input type="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="البريد الإلكتروني"
                       value="{{ old('email') }}"
                       autocomplete="email"
                       required>
            </div>

            {{-- كلمة المرور --}}
            <div class="field-wrap">
                <i class="fas fa-lock field-icon"></i>
                <input type="password"
                       name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="كلمة المرور"
                       autocomplete="current-password"
                       required>
            </div>

            {{-- تذكرني --}}
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="d-flex align-items-center gap-2" style="cursor:pointer;font-size:.85rem;color:#666">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    تذكرني
                </label>
                <a href="#" style="font-size:.82rem;color:#2C5AA0;text-decoration:none">
                    نسيت كلمة المرور؟
                </a>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>تسجيل الدخول
            </button>
        </form>

        <div class="divider">أو</div>

        <div class="register-link">
            ليس لديك حساب؟
            <a href="{{ route('register') }}">إنشاء حساب جديد</a>
        </div>

    </div>
</div>

@push('scripts')
<script>
    function setType(type) {
        document.getElementById('loginType').value = type;
        document.querySelectorAll('.type-tab').forEach(btn => btn.classList.remove('active'));
        event.currentTarget.classList.add('active');
    }
</script>
@endpush
@endsection
