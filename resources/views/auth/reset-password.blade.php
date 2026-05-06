@extends('layouts.app')

@section('title', 'تعيين كلمة مرور جديدة')

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
    .toggle-password {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #bbb;
        font-size: .9rem;
        cursor: pointer;
        z-index: 5;
        background: none;
        border: none;
        padding: 0;
    }
    .toggle-password:hover { color: #2C5AA0; }

    .form-control {
        padding-right: 38px;
        padding-left: 38px;
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

    .password-strength {
        height: 4px;
        border-radius: 2px;
        background: #eee;
        margin-top: .35rem;
        overflow: hidden;
    }
    .password-strength-bar {
        height: 100%;
        width: 0%;
        border-radius: 2px;
        transition: width .3s, background .3s;
    }
    .strength-label {
        font-size: .75rem;
        color: #999;
        margin-top: .2rem;
    }

    .back-link {
        text-align: center;
        font-size: .88rem;
        color: #777;
        margin-top: 1.25rem;
    }
    .back-link a { color: #2C5AA0; font-weight: 600; text-decoration: none; }
    .back-link a:hover { text-decoration: underline; }
</style>
@endpush

@section('content')
<div class="login-wrapper">
    <div class="login-card">

        {{-- Logo --}}
        <div class="login-logo">
            <div class="icon-wrap"><i class="fas fa-lock-open"></i></div>
            <h4>تعيين كلمة مرور جديدة</h4>
            <p>اختر كلمة مرور قوية لحسابك</p>
        </div>

        {{-- أخطاء --}}
        @if($errors->any())
            <div class="alert alert-danger py-2 mb-3" style="border-radius:10px; border:none; font-size:.88rem">
                <i class="fas fa-exclamation-circle me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        {{-- النموذج --}}
        <form action="{{ $type === 'company' ? route('company.password.update') : route('password.update') }}"
              method="POST">
            @csrf

            {{-- token مخفي — مطلوب للتحقق --}}
            <input type="hidden" name="token" value="{{ $token }}">

            {{-- البريد الإلكتروني --}}
            <div class="field-wrap">
                <i class="fas fa-envelope field-icon"></i>
                <input type="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="البريد الإلكتروني"
                       value="{{ old('email', $email) }}"
                       autocomplete="email"
                       required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- كلمة المرور الجديدة --}}
            <div class="field-wrap">
                <i class="fas fa-lock field-icon"></i>
                <input type="password"
                       id="newPassword"
                       name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="كلمة المرور الجديدة (8 أحرف على الأقل)"
                       autocomplete="new-password"
                       oninput="checkStrength(this.value)"
                       required>
                <button type="button" class="toggle-password" onclick="togglePass('newPassword', this)">
                    <i class="fas fa-eye"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                {{-- مؤشر قوة كلمة المرور --}}
                <div class="password-strength">
                    <div class="password-strength-bar" id="strengthBar"></div>
                </div>
                <div class="strength-label" id="strengthLabel"></div>
            </div>

            {{-- تأكيد كلمة المرور --}}
            <div class="field-wrap">
                <i class="fas fa-lock field-icon"></i>
                <input type="password"
                       id="confirmPassword"
                       name="password_confirmation"
                       class="form-control @error('password_confirmation') is-invalid @enderror"
                       placeholder="تأكيد كلمة المرور"
                       autocomplete="new-password"
                       required>
                <button type="button" class="toggle-password" onclick="togglePass('confirmPassword', this)">
                    <i class="fas fa-eye"></i>
                </button>
                @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-check me-2"></i>تعيين كلمة المرور
            </button>
        </form>

        <div class="back-link">
            <a href="{{ route('login') }}">
                <i class="fas fa-arrow-right me-1"></i>العودة لتسجيل الدخول
            </a>
        </div>

    </div>
</div>

@push('scripts')
<script>
    // إظهار/إخفاء كلمة المرور
    function togglePass(fieldId, btn) {
        const input = document.getElementById(fieldId);
        const icon  = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // مؤشر قوة كلمة المرور
    function checkStrength(val) {
        const bar   = document.getElementById('strengthBar');
        const label = document.getElementById('strengthLabel');
        let score   = 0;

        if (val.length >= 8)  score++;
        if (val.length >= 12) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = [
            { width: '20%', color: '#dc3545', text: 'ضعيفة جداً' },
            { width: '40%', color: '#fd7e14', text: 'ضعيفة' },
            { width: '60%', color: '#ffc107', text: 'متوسطة' },
            { width: '80%', color: '#20c997', text: 'جيدة' },
            { width: '100%',color: '#198754', text: 'قوية جداً' },
        ];

        const level = levels[Math.min(score, 4)];
        bar.style.width      = val.length ? level.width : '0%';
        bar.style.background = level.color;
        label.textContent    = val.length ? level.text : '';
        label.style.color    = level.color;
    }
</script>
@endpush
@endsection