@extends('layouts.app')

@section('title', 'تحقق من بريدك الإلكتروني')

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
        max-width: 480px;
        padding: 2.5rem 2rem;
        animation: slideUp .4s ease;
        text-align: center;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .icon-wrap {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #2C5AA0, #1e4085);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: #fff;
        margin-bottom: 1.25rem;
    }

    h4 {
        font-weight: 700;
        color: #333;
        margin-bottom: .5rem;
    }

    .subtitle {
        color: #777;
        font-size: .9rem;
        line-height: 1.6;
        margin-bottom: 1.75rem;
    }

    .email-badge {
        display: inline-block;
        background: #f0f5ff;
        border: 1px solid #d0e0ff;
        color: #2C5AA0;
        font-weight: 600;
        border-radius: 8px;
        padding: .4rem 1rem;
        font-size: .9rem;
        margin-bottom: 1.75rem;
        word-break: break-all;
    }

    .btn-resend {
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
    }
    .btn-resend:hover { opacity: .92; transform: translateY(-1px); }
    .btn-resend:active { transform: translateY(0); }

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

    .steps {
        background: #f8faff;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        text-align: right;
        margin-bottom: 1.5rem;
    }
    .step-item {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        padding: .4rem 0;
        font-size: .85rem;
        color: #555;
    }
    .step-num {
        width: 22px;
        height: 22px;
        background: #2C5AA0;
        color: #fff;
        border-radius: 50%;
        font-size: .72rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .logout-link {
        font-size: .82rem;
        color: #aaa;
        text-decoration: none;
    }
    .logout-link:hover { color: #dc3545; }
</style>
@endpush

@section('content')
<div class="login-wrapper">
    <div class="login-card">

        {{-- أيقونة --}}
        <div class="icon-wrap">
            <i class="fas fa-envelope-open-text"></i>
        </div>

        <h4>تحقق من بريدك الإلكتروني</h4>
        <p class="subtitle">
            أرسلنا لك رابط تفعيل الحساب على البريد الإلكتروني
        </p>

        {{-- عرض الإيميل --}}
        <div class="email-badge">
            <i class="fas fa-envelope me-1"></i>
            {{ auth()->user()->email }}
        </div>

        {{-- رسائل --}}
        @if(session('success'))
            <div class="alert alert-success py-2 mb-3" style="border-radius:10px; border:none; font-size:.88rem; text-align:right">
                <i class="fas fa-check-circle me-1"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info py-2 mb-3" style="border-radius:10px; border:none; font-size:.88rem; text-align:right">
                <i class="fas fa-info-circle me-1"></i>
                {{ session('info') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger py-2 mb-3" style="border-radius:10px; border:none; font-size:.88rem; text-align:right">
                <i class="fas fa-exclamation-circle me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        {{-- خطوات للمساعدة --}}
        <div class="steps">
            <div class="step-item">
                <div class="step-num">1</div>
                <span>افتح بريدك الإلكتروني وابحث عن رسالة من <strong>منصة خطوة</strong></span>
            </div>
            <div class="step-item">
                <div class="step-num">2</div>
                <span>اضغط على زر <strong>"تفعيل الحساب"</strong> داخل الرسالة</span>
            </div>
            <div class="step-item">
                <div class="step-num">3</div>
                <span>إذا لم تجد الرسالة، تحقق من مجلد <strong>Spam / البريد غير المرغوب</strong></span>
            </div>
        </div>

        {{-- إعادة الإرسال --}}
        <form action="{{ route('verification.send') }}" method="POST">
            @csrf
            <button type="submit" class="btn-resend">
                <i class="fas fa-paper-plane me-2"></i>إعادة إرسال رابط التفعيل
            </button>
        </form>

        <div class="divider">أو</div>

        {{-- تسجيل الخروج --}}
        <form action="{{ route('logout') }}" method="POST" style="display:inline">
            @csrf
            <button type="submit" class="logout-link" style="background:none; border:none; cursor:pointer">
                <i class="fas fa-sign-out-alt me-1"></i>تسجيل الخروج والعودة للصفحة الرئيسية
            </button>
        </form>

    </div>
</div>
@endsection