@extends('layouts.app')

@section('title', 'إنشاء حساب')

@push('styles')
<style>
    body { background: linear-gradient(135deg, #2C5AA0 0%, #1e4085 100%); min-height: 100vh; }
    .navbar-khutwa { display: none; }

    .register-wrapper {
        min-height: 100vh;
        display: flex; align-items: center; justify-content: center;
        padding: 2rem 1rem;
    }
    .register-card {
        background: #fff; border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,.25);
        width: 100%; max-width: 520px;
        padding: 2rem 2rem 2.5rem;
        animation: slideUp .4s ease;
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .register-logo { text-align: center; margin-bottom: 1.5rem; }
    .register-logo .icon-wrap {
        width: 60px; height: 60px;
        background: linear-gradient(135deg, #2C5AA0, #1e4085);
        border-radius: 15px; display: inline-flex;
        align-items: center; justify-content: center;
        font-size: 1.5rem; color: #fff; margin-bottom: .6rem;
    }
    .register-logo h4 { font-weight: 700; color: #333; margin: 0; font-size: 1.2rem; }

    .type-tabs {
        display: flex; background: #f4f6fb; border-radius: 10px;
        padding: 4px; margin-bottom: 1.5rem; gap: 4px;
    }
    .type-tab {
        flex: 1; padding: .6rem; text-align: center;
        border-radius: 8px; border: none; background: transparent;
        color: #888; font-size: .88rem; font-family: inherit;
        font-weight: 500; cursor: pointer; transition: all .25s;
    }
    .type-tab.active {
        background: #fff; color: #2C5AA0;
        box-shadow: 0 2px 8px rgba(0,0,0,.1); font-weight: 700;
    }
    .field-wrap { position: relative; margin-bottom: .9rem; }
    .field-icon {
        position: absolute; right: 13px; top: 50%;
        transform: translateY(-50%); color: #ccc;
        font-size: .85rem; z-index: 5; pointer-events: none;
    }
    .form-control, .form-select {
        padding-right: 36px; border-radius: 9px;
        border: 1.5px solid #e5e7eb; height: 44px;
        font-size: .88rem; transition: border-color .2s; font-family: inherit;
    }
    .form-control:focus, .form-select:focus {
        border-color: #2C5AA0; box-shadow: 0 0 0 3px rgba(44,90,160,.1);
    }
    .form-control.is-invalid { border-color: #dc3545; }
    .row-fields { display: flex; gap: .75rem; }
    .row-fields > div { flex: 1; }
    .section-label {
        font-size: .7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; color: #aaa; margin: 1.1rem 0 .6rem;
    }
    .btn-register {
        width: 100%; height: 46px;
        background: linear-gradient(135deg, #2C5AA0, #1e4085);
        border: none; border-radius: 9px; color: #fff;
        font-size: .95rem; font-weight: 700; font-family: inherit;
        cursor: pointer; transition: all .25s; margin-top: .5rem;
    }
    .btn-register:hover { opacity: .9; transform: translateY(-1px); }
    .tab-pane { display: none; }
    .tab-pane.active { display: block; }
    .login-link { text-align: center; font-size: .85rem; color: #888; margin-top: 1rem; }
    .login-link a { color: #2C5AA0; font-weight: 600; text-decoration: none; }
</style>
@endpush

@section('content')
<div class="register-wrapper">
    <div class="register-card">

        <div class="register-logo">
            <div class="icon-wrap"><i class="fas fa-shoe-prints"></i></div>
            <h4>إنشاء حساب جديد</h4>
        </div>

        <div class="type-tabs">
            <button type="button" class="type-tab active" id="tab-btn-user" onclick="switchTab('user')">
                <i class="fas fa-user me-1"></i>باحث عمل
            </button>
            <button type="button" class="type-tab" id="tab-btn-company" onclick="switchTab('company')">
                <i class="fas fa-building me-1"></i>شركة / جهة توظيف
            </button>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2 mb-3"
                 style="border-radius:9px;border:none;font-size:.85rem">
                <i class="fas fa-exclamation-circle me-1"></i>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ========== نموذج باحث العمل ========== --}}
        <div class="tab-pane active" id="tab-user">
            <form action="{{ route('register.user') }}" method="POST">
                @csrf
                <input type="hidden" name="_type" value="user">

                <div class="row-fields">
                    <div class="field-wrap">
                        <i class="fas fa-user field-icon"></i>
                        <input type="text" name="username"
                               class="form-control @error('username') is-invalid @enderror"
                               placeholder="اسم المستخدم *"
                               value="{{ old('username') }}" required>
                    </div>
                    <div class="field-wrap">
                        <i class="fas fa-id-card field-icon"></i>
                        <input type="text" name="full_name"
                               class="form-control"
                               placeholder="الاسم الكامل"
                               value="{{ old('full_name') }}">
                    </div>
                </div>

                <div class="field-wrap">
                    <i class="fas fa-envelope field-icon"></i>
                    <input type="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="البريد الإلكتروني *"
                           value="{{ old('email') }}" required>
                </div>

                <div class="field-wrap">
                    <i class="fas fa-phone field-icon"></i>
                    <input type="text" name="phone"
                           class="form-control @error('phone') is-invalid @enderror"
                           placeholder="رقم الهاتف *"
                           value="{{ old('phone') }}" required>
                </div>

                <div class="row-fields">
                    <div class="field-wrap">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="كلمة المرور *" required>
                    </div>
                    <div class="field-wrap">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" name="password_confirmation"
                               class="form-control" placeholder="تأكيد كلمة المرور *" required>
                    </div>
                </div>

                <div style="font-size:.76rem;color:#aaa;margin-bottom:.75rem">
                    <i class="fas fa-shield-alt me-1"></i>كلمة المرور: 8 أحرف على الأقل
                </div>

                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus me-2"></i>إنشاء حساب باحث عمل
                </button>
            </form>
        </div>

        {{-- ========== نموذج الشركة ========== --}}
        <div class="tab-pane" id="tab-company">
            <form action="{{ route('register.company') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_type" value="company">

                <div class="section-label">معلومات الشركة</div>

                <div class="field-wrap">
                    <i class="fas fa-building field-icon"></i>
                    <input type="text" name="company_name"
                           class="form-control @error('company_name') is-invalid @enderror"
                           placeholder="اسم الشركة / الجهة *"
                           value="{{ old('company_name') }}" required>
                </div>

                <div class="row-fields">
                    <div class="field-wrap">
                        <i class="fas fa-industry field-icon"></i>
                        <select name="industry" class="form-select">
                            <option value="">القطاع (اختياري)</option>
                            @foreach([
                                'technology'   => 'تكنولوجيا المعلومات',
                                'finance'      => 'المالية والمحاسبة',
                                'education'    => 'التعليم',
                                'healthcare'   => 'الصحة والطب',
                                'construction' => 'الإنشاء والمقاولات',
                                'trade'        => 'التجارة والمبيعات',
                                'media'        => 'الإعلام والتسويق',
                                'ngo'          => 'منظمات غير حكومية',
                                'government'   => 'جهة حكومية',
                                'other'        => 'أخرى',
                            ] as $val => $label)
                                <option value="{{ $val }}" {{ old('industry') === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-wrap">
                        <i class="fas fa-users field-icon"></i>
                        <select name="company_size" class="form-select">
                            <option value="startup">ناشئة (1-10)</option>
                            <option value="small" selected>صغيرة (11-50)</option>
                            <option value="medium">متوسطة (51-200)</option>
                            <option value="large">كبيرة (+200)</option>
                        </select>
                    </div>
                </div>

                <div class="section-label">بيانات التواصل</div>

                <div class="field-wrap">
                    <i class="fas fa-envelope field-icon"></i>
                    <input type="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="البريد الإلكتروني الرسمي *"
                           value="{{ old('email') }}" required>
                </div>

                <div class="row-fields">
                    <div class="field-wrap">
                        <i class="fas fa-phone field-icon"></i>
                        <input type="text" name="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               placeholder="رقم الهاتف *"
                               value="{{ old('phone') }}" required>
                    </div>
                    <div class="field-wrap">
                        <i class="fas fa-globe field-icon"></i>
                        <input type="url" name="website"
                               class="form-control"
                               placeholder="الموقع الإلكتروني"
                               value="{{ old('website') }}">
                    </div>
                </div>

                <div class="section-label">كلمة المرور</div>

                <div class="row-fields">
                    <div class="field-wrap">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="كلمة المرور *" required>
                    </div>
                    <div class="field-wrap">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" name="password_confirmation"
                               class="form-control" placeholder="تأكيد المرور *" required>
                    </div>
                </div>

                <div style="font-size:.76rem;color:#888;margin-bottom:.75rem;background:#fffbeb;padding:.5rem .75rem;border-radius:7px">
                    <i class="fas fa-clock me-1 text-warning"></i>
                    سيتم مراجعة الحساب من الإدارة قبل التفعيل الكامل
                </div>

                <button type="submit" class="btn-register">
                    <i class="fas fa-building me-2"></i>تسجيل الشركة
                </button>
            </form>
        </div>

        <div class="login-link">
            لديك حساب بالفعل؟ <a href="{{ route('login') }}">تسجيل الدخول</a>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function switchTab(type) {
    ['user','company'].forEach(t => {
        document.getElementById('tab-' + t).classList.remove('active');
        document.getElementById('tab-btn-' + t).classList.remove('active');
    });
    document.getElementById('tab-' + type).classList.add('active');
    document.getElementById('tab-btn-' + type).classList.add('active');
}
// فتح تبويب الشركة إذا كانت الأخطاء تخصه
@if(old('_type') === 'company' || $errors->has('company_name'))
    document.addEventListener('DOMContentLoaded', () => switchTab('company'));
@endif
</script>
@endpush
