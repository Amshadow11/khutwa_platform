@extends('layouts.app')
@section('title', 'تعديل الملف الشخصي')

@push('styles')
<style>
    .page-wrap { padding: 1.5rem 0 3rem; }
    .avatar-wrap { position:relative; display:inline-block; }
    .avatar-img  { width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid #e9ecef; }
    .avatar-edit {
        position:absolute; bottom:0; left:0;
        width:28px; height:28px; border-radius:50%;
        background:#2C5AA0; color:#fff;
        display:flex;align-items:center;justify-content:center;
        font-size:.7rem; cursor:pointer; border:2px solid #fff;
    }
    .section-label {
        font-size:.7rem;font-weight:700;text-transform:uppercase;
        letter-spacing:.06em;color:#aaa;
        margin:1.5rem 0 .75rem; padding-bottom:.4rem;
        border-bottom:1px solid #f0f0f0;
    }
    .form-control, .form-select {
        border-radius:9px; border:1.5px solid #e5e7eb;
        font-family:inherit; font-size:.88rem;
    }
    .form-control:focus, .form-select:focus {
        border-color:#2C5AA0; box-shadow:0 0 0 3px rgba(44,90,160,.1);
    }
</style>
@endpush

@section('content')
<div class="page-wrap">
<div class="container">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('user.profile.show') }}" class="text-muted">
            <i class="fas fa-arrow-right"></i>
        </a>
        <h4 class="fw-bold mb-0">تعديل الملف الشخصي</h4>
    </div>

    <div class="row justify-content-center">
    <div class="col-12 col-lg-8">

    {{-- ===== معلومات أساسية ===== --}}
    <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-4">

                {{-- الصورة الشخصية --}}
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="avatar-wrap">
                        <img src="{{ $user->avatar_url }}" class="avatar-img" id="avatarPreview" alt="">
                        <div class="avatar-edit" onclick="document.getElementById('picInput').click()">
                            <i class="fas fa-camera"></i>
                        </div>
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $user->display_name }}</div>
                        <div class="text-muted small">اضغط على الكاميرا لتغيير الصورة</div>
                        <div class="text-muted" style="font-size:.72rem">JPG, PNG, WEBP — أقل من 2MB</div>
                    </div>
                    <input type="file" id="picInput" name="profile_picture"
                           accept="image/jpeg,image/png,image/webp" hidden
                           onchange="previewAvatar(this)">
                </div>

                <div class="section-label">المعلومات الأساسية</div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">اسم المستخدم <span class="text-danger">*</span></label>
                        <input type="text" name="username"
                               class="form-control @error('username') is-invalid @enderror"
                               value="{{ old('username', $user->username) }}" required>
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الاسم الكامل</label>
                        <input type="text" name="full_name"
                               class="form-control @error('full_name') is-invalid @enderror"
                               value="{{ old('full_name', $user->full_name) }}">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">رقم الهاتف <span class="text-danger">*</span></label>
                        <input type="text" name="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $user->phone) }}" required>
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">تاريخ الميلاد</label>
                        <input type="date" name="birth_date"
                               class="form-control @error('birth_date') is-invalid @enderror"
                               value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الجنس</label>
                        <select name="gender" class="form-select">
                            <option value="">غير محدد</option>
                            <option value="male"   {{ old('gender',$user->gender)==='male'   ?'selected':'' }}>ذكر</option>
                            <option value="female" {{ old('gender',$user->gender)==='female' ?'selected':'' }}>أنثى</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">العنوان / المدينة</label>
                        <input type="text" name="address"
                               class="form-control"
                               value="{{ old('address', $user->address) }}"
                               placeholder="مثال: صنعاء، حدة">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">نبذة تعريفية</label>
                    <textarea name="bio" class="form-control" rows="3"
                              placeholder="اكتب نبذة قصيرة عنك وعن مسيرتك المهنية...">{{ old('bio', $user->bio) }}</textarea>
                    <div class="form-text">تظهر للشركات عند مراجعة طلبك. الحد الأقصى 1000 حرف.</div>
                </div>

                <div class="section-label">المعلومات المهنية</div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">المهارات</label>
                    <input type="text" name="skills"
                           class="form-control"
                           value="{{ old('skills', $user->skills) }}"
                           placeholder="مثال: PHP، Laravel، MySQL، JavaScript">
                    <div class="form-text">افصل المهارات بفاصلة</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">الخبرات العملية</label>
                    <textarea name="experience" class="form-control" rows="3"
                              placeholder="اذكر أبرز خبراتك وتجاربك السابقة...">{{ old('experience', $user->experience) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">المؤهلات الدراسية</label>
                    <textarea name="education" class="form-control" rows="2"
                              placeholder="مثال: بكالوريوس هندسة حاسوب — جامعة صنعاء — 2020">{{ old('education', $user->education) }}</textarea>
                </div>

                <div class="section-label">الروابط المهنية</div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fab fa-linkedin text-primary me-1"></i>LinkedIn
                        </label>
                        <input type="url" name="linkedin_url"
                               class="form-control @error('linkedin_url') is-invalid @enderror"
                               value="{{ old('linkedin_url', $user->linkedin_url) }}"
                               placeholder="https://linkedin.com/in/...">
                        @error('linkedin_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fab fa-github me-1"></i>GitHub
                        </label>
                        <input type="url" name="github_url"
                               class="form-control @error('github_url') is-invalid @enderror"
                               value="{{ old('github_url', $user->github_url) }}"
                               placeholder="https://github.com/...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-globe me-1"></i>Portfolio
                        </label>
                        <input type="url" name="portfolio_url"
                               class="form-control @error('portfolio_url') is-invalid @enderror"
                               value="{{ old('portfolio_url', $user->portfolio_url) }}"
                               placeholder="https://...">
                    </div>
                </div>

            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('user.profile.show') }}"
               class="btn btn-outline-secondary rounded-pill px-4">إلغاء</a>
            <button type="submit" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-save me-1"></i>حفظ التغييرات
            </button>
        </div>
    </form>

    {{-- ===== تغيير كلمة المرور ===== --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-transparent fw-bold">
            <i class="fas fa-lock me-2 text-primary"></i>تغيير كلمة المرور
        </div>
        <div class="card-body p-4">
            <form action="{{ route('user.profile.password') }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">كلمة المرور الحالية</label>
                        <input type="password" name="current_password"
                               class="form-control @error('current_password') is-invalid @enderror"
                               required>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">كلمة المرور الجديدة</label>
                        <input type="password" name="new_password"
                               class="form-control @error('new_password') is-invalid @enderror"
                               required>
                        @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">تأكيد كلمة المرور الجديدة</label>
                        <input type="password" name="new_password_confirmation"
                               class="form-control" required>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-warning rounded-pill px-4 btn-sm">
                        <i class="fas fa-key me-1"></i>تغيير كلمة المرور
                    </button>
                </div>
            </form>
        </div>
    </div>

    </div>
    </div>
</div>
</div>

@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
