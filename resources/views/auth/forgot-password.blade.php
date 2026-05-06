@extends('layouts.app')

@section('title', 'نسيت كلمة المرور')

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

    .btn-submit {
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
    .btn-submit:hover { opacity: .92; transform: translateY(-1px); }
    .btn-submit:active { transform: translateY(0); }

    .back-link {
        text-align: center;
        font-size: .88rem;
        color: #777;
        margin-top: 1.25rem;
    }
    .back-link a { color: #2C5AA0; font-weight: 600; text-decoration: none; }
    .back-link a:hover { text-decoration: underline; }

    .info-box {
        background: #f0f5ff;
        border-right: 3px solid #2C5AA0;
        border-radius: 8px;
        padding: .85rem 1rem;
        font-size: .85rem;
        color: #555;
        margin-bottom: 1.25rem;
        line-height: 1.6;
    }
</style>
@endpush

@section('content')
<div class="login-wrapper">
    <div class="login-card">

        {{-- Logo --}}
        <div class="login-logo">
            <div class="icon-wrap"><i class="fas fa-key"></i></div>
            <h4>نسيت كلمة المرور؟</h4>
            <p>{{ $type === 'company' ? 'استعادة حساب الشركة' : 'استعادة حساب باحث العمل' }}</p>
        </div>

        {{-- رسالة النجاح --}}
        @if(session('success'))
            <div class="alert alert-success py-2 mb-3" style="border-radius:10px; border:none; font-size:.88rem">
                <i class="fas fa-check-circle me-1"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- رسالة الخطأ --}}
        @if($errors->any())
            <div class="alert alert-danger py-2 mb-3" style="border-radius:10px; border:none; font-size:.88rem">
                <i class="fas fa-exclamation-circle me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        {{-- شرح العملية --}}
        <div class="info-box">
            <i class="fas fa-info-circle me-1" style="color:#2C5AA0"></i>
            أدخل بريدك الإلكتروني المسجّل وسنرسل لك رابطاً لإعادة تعيين كلمة المرور.
        </div>

        {{-- النموذج --}}
        @if(! session('success'))
            <form action="{{ $type === 'company' ? route('company.password.email') : route('password.email') }}"
                  method="POST">
                @csrf

                <div class="field-wrap">
                    <i class="fas fa-envelope field-icon"></i>
                    <input type="email"
                           name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="البريد الإلكتروني"
                           value="{{ old('email') }}"
                           autocomplete="email"
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane me-2"></i>إرسال رابط الاستعادة
                </button>
            </form>
        @endif

        <div class="back-link">
            <a href="{{ route('login') }}">
                <i class="fas fa-arrow-right me-1"></i>العودة لتسجيل الدخول
            </a>
        </div>

    </div>
</div>
@endsection