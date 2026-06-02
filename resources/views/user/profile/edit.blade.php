@extends('layouts.app')
@section('title', 'تعديل الملف المهني')

@push('styles')
<style>
    .page-wrap { padding: 1.5rem 0 3rem; }
    .profile-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; }
    .panel { background: #fff; border-radius: 12px; padding: 1.25rem; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
    .panel-title { font-weight: 800; color: #2C5AA0; margin-bottom: 1rem; }
    .form-control, .form-select { border-radius: 9px; border: 1.5px solid #e5e7eb; font-size: .9rem; }
    .item-row { border-top: 1px solid #f0f0f0; padding-top: .85rem; margin-top: .85rem; }
    .avatar-img { width: 84px; height: 84px; border-radius: 50%; object-fit: cover; border: 3px solid #e9ecef; }
    @media (min-width: 992px) { .profile-grid { grid-template-columns: 1fr 1fr; } .full-panel { grid-column: 1 / -1; } }
</style>
@endpush

@section('content')
<div class="page-wrap">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">تعديل الملف المهني</h4>
                <div class="text-muted small">البيانات هنا أصبحت منظمة وجاهزة للسيرة الذاتية والبحث والمطابقة الذكية.</div>
            </div>
            <a href="{{ route('user.profile.show') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">رجوع</a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <div class="fw-bold mb-1">راجع الحقول التالية:</div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="profile-grid">
            <div class="panel full-panel">
                <div class="panel-title">البيانات الأساسية والروابط</div>
                <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-auto">
                            <img src="{{ $user->avatar_url }}" class="avatar-img" id="avatarPreview" alt="">
                        </div>
                        <div class="col">
                            <input type="file" id="picInput" name="profile_picture" accept="image/jpeg,image/png,image/webp" class="form-control" onchange="previewAvatar(this)">
                            <div class="form-text">JPG, PNG, WEBP - أقل من 2MB</div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">اسم المستخدم</label>
                            <input name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الاسم الكامل</label>
                            <input name="full_name" class="form-control" value="{{ old('full_name', $user->full_name) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الجوال</label>
                            <input name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">تاريخ الميلاد</label>
                            <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الجنس</label>
                            <select name="gender" class="form-select">
                                <option value="">غير محدد</option>
                                <option value="male" @selected(old('gender', $user->gender) === 'male')>ذكر</option>
                                <option value="female" @selected(old('gender', $user->gender) === 'female')>أنثى</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">العنوان</label>
                            <input name="address" class="form-control" value="{{ old('address', $user->address) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">نبذة تعريفية</label>
                            <textarea name="bio" rows="3" class="form-control">{{ old('bio', $user->bio) }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">LinkedIn</label>
                            <input type="url" name="linkedin_url" class="form-control" value="{{ old('linkedin_url', $user->linkedin_url) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">GitHub</label>
                            <input type="url" name="github_url" class="form-control" value="{{ old('github_url', $user->github_url) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Portfolio</label>
                            <input type="url" name="portfolio_url" class="form-control" value="{{ old('portfolio_url', $user->portfolio_url) }}">
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3 rounded-pill px-4">حفظ البيانات الأساسية</button>
                </form>
            </div>

            <div class="panel full-panel">
                <div class="panel-title">الهوية المهنية والرؤية العامة</div>
                <form action="{{ route('user.profile.professional.update') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">العنوان المهني</label>
                            <input name="headline" class="form-control" value="{{ old('headline', $profile->headline) }}" placeholder="Laravel Developer | Backend Engineer">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">المسمى الحالي</label>
                            <input name="current_title" class="form-control" value="{{ old('current_title', $profile->current_title) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الشركة الحالية</label>
                            <input name="current_company" class="form-control" value="{{ old('current_company', $profile->current_company) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">المجال</label>
                            <input name="industry" class="form-control" value="{{ old('industry', $profile->industry) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">مستوى الخبرة</label>
                            <select name="seniority_level" class="form-select">
                                <option value="">غير محدد</option>
                                @foreach(['junior','mid','senior','lead','manager','executive'] as $level)
                                    <option value="{{ $level }}" @selected(old('seniority_level', $profile->seniority_level) === $level)>{{ ucfirst($level) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الدولة ISO</label>
                            <input name="location_country" maxlength="2" class="form-control" value="{{ old('location_country', $profile->location_country) }}" placeholder="SA">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">المدينة</label>
                            <input name="location_city" class="form-control" value="{{ old('location_city', $profile->location_city) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">ظهور الملف</label>
                            <select name="profile_visibility" class="form-select">
                                <option value="public" @selected(old('profile_visibility', $profile->profile_visibility) === 'public')>عام</option>
                                <option value="private" @selected(old('profile_visibility', $profile->profile_visibility) === 'private')>خاص</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="open_to_work" value="1" @checked(old('open_to_work', $profile->open_to_work))>
                                <span class="form-check-label">متاح للعمل</span>
                            </label>
                        </div>
                        <div class="col-12">
                            <label class="form-label">الأقسام الظاهرة للعامة</label>
                            <div class="d-flex gap-3 flex-wrap">
                                @foreach(['about' => 'نبذة', 'skills' => 'مهارات', 'experience' => 'خبرات', 'education' => 'تعليم', 'projects' => 'مشاريع', 'certifications' => 'شهادات', 'languages' => 'لغات', 'links' => 'روابط'] as $key => $label)
                                    <label class="form-check">
                                        <input class="form-check-input" type="checkbox" name="public_sections[]" value="{{ $key }}" @checked(in_array($key, old('public_sections', $profile->public_sections ?? []), true) || empty($profile->public_sections))>
                                        <span class="form-check-label">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3 rounded-pill px-4">حفظ الهوية المهنية</button>
                </form>
            </div>

            <div class="panel">
                <div class="panel-title">المهارات</div>
                <form action="{{ route('user.profile.skills.store') }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-12"><input name="name" class="form-control" placeholder="مثال: Laravel" required></div>
                    <div class="col-6"><input name="years_experience" type="number" step="0.5" min="0" class="form-control" placeholder="سنوات"></div>
                    <div class="col-6">
                        <select name="proficiency_level" class="form-select">
                            <option value="">المستوى</option>
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                            <option value="expert">Expert</option>
                        </select>
                    </div>
                    <div class="col-12"><button class="btn btn-sm btn-primary rounded-pill px-3">إضافة مهارة</button></div>
                </form>
                @foreach($user->canonicalSkills as $skill)
                    <div class="item-row d-flex justify-content-between gap-2">
                        <div>
                            <div class="fw-semibold">{{ $skill->name }}</div>
                            <div class="text-muted small">{{ $skill->pivot?->proficiency_level }} {{ $skill->pivot?->years_experience ? '· '.$skill->pivot->years_experience.' years' : '' }}</div>
                        </div>
                        <form action="{{ route('user.profile.skills.destroy', $skill->pivot->id) }}" method="POST">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">حذف</button></form>
                    </div>
                @endforeach
            </div>

            <div class="panel">
                <div class="panel-title">اللغات</div>
                <form action="{{ route('user.profile.languages.store') }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-7">
                        <select name="language_id" class="form-select" required>
                            <option value="">اختر اللغة</option>
                            @foreach($languages as $language)
                                <option value="{{ $language->id }}">{{ $language->native_name ?: $language->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-5"><input name="proficiency_level" class="form-control" placeholder="Fluent"></div>
                    <div class="col-12"><button class="btn btn-sm btn-primary rounded-pill px-3">إضافة لغة</button></div>
                </form>
                @foreach($user->languages as $language)
                    <div class="item-row d-flex justify-content-between">
                        <div>{{ $language->native_name ?: $language->name }} <span class="text-muted small">{{ $language->pivot?->proficiency_level }}</span></div>
                        <form action="{{ route('user.profile.languages.destroy', $language->pivot->id) }}" method="POST">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">حذف</button></form>
                    </div>
                @endforeach
            </div>

            <div class="panel">
                <div class="panel-title">إضافة خبرة</div>
                <form action="{{ route('user.profile.experiences.store') }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-12"><input name="title" class="form-control" placeholder="المسمى الوظيفي" required></div>
                    <div class="col-12"><input name="company_name" class="form-control" placeholder="الشركة"></div>
                    <div class="col-6"><input type="date" name="start_date" class="form-control"></div>
                    <div class="col-6"><input type="date" name="end_date" class="form-control"></div>
                    <div class="col-12"><textarea name="summary" rows="3" class="form-control" placeholder="الإنجازات والمسؤوليات"></textarea></div>
                    <div class="col-12"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_current" value="1"> <span class="form-check-label">وظيفة حالية</span></label></div>
                    <div class="col-12"><button class="btn btn-sm btn-primary rounded-pill px-3">إضافة خبرة</button></div>
                </form>
                @foreach($user->experiences as $experience)
                    <div class="item-row d-flex justify-content-between gap-2">
                        <div><div class="fw-semibold">{{ $experience->title }}</div><div class="text-muted small">{{ $experience->company_name }}</div></div>
                        <form action="{{ route('user.profile.experiences.destroy', $experience) }}" method="POST">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">حذف</button></form>
                    </div>
                @endforeach
            </div>

            <div class="panel">
                <div class="panel-title">إضافة تعليم</div>
                <form action="{{ route('user.profile.educations.store') }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-12"><input name="institution_name" class="form-control" placeholder="الجامعة / المؤسسة" required></div>
                    <div class="col-6"><input name="degree" class="form-control" placeholder="الدرجة"></div>
                    <div class="col-6"><input name="field_of_study" class="form-control" placeholder="التخصص"></div>
                    <div class="col-12"><textarea name="description" rows="2" class="form-control" placeholder="وصف مختصر"></textarea></div>
                    <div class="col-12"><button class="btn btn-sm btn-primary rounded-pill px-3">إضافة تعليم</button></div>
                </form>
                @foreach($user->educations as $education)
                    <div class="item-row d-flex justify-content-between gap-2">
                        <div><div class="fw-semibold">{{ $education->institution_name }}</div><div class="text-muted small">{{ $education->degree }}</div></div>
                        <form action="{{ route('user.profile.educations.destroy', $education) }}" method="POST">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">حذف</button></form>
                    </div>
                @endforeach
            </div>

            <div class="panel">
                <div class="panel-title">إضافة مشروع</div>
                <form action="{{ route('user.profile.projects.store') }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-12"><input name="title" class="form-control" placeholder="اسم المشروع" required></div>
                    <div class="col-12"><textarea name="description" rows="2" class="form-control" placeholder="وصف المشروع"></textarea></div>
                    <div class="col-6"><input type="url" name="project_url" class="form-control" placeholder="Project URL"></div>
                    <div class="col-6"><input type="url" name="repository_url" class="form-control" placeholder="Repository URL"></div>
                    <div class="col-12"><button class="btn btn-sm btn-primary rounded-pill px-3">إضافة مشروع</button></div>
                </form>
                @foreach($user->projects as $project)
                    <div class="item-row d-flex justify-content-between gap-2">
                        <div class="fw-semibold">{{ $project->title }}</div>
                        <form action="{{ route('user.profile.projects.destroy', $project) }}" method="POST">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">حذف</button></form>
                    </div>
                @endforeach
            </div>

            <div class="panel">
                <div class="panel-title">إضافة شهادة</div>
                <form action="{{ route('user.profile.certifications.store') }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-12"><input name="name" class="form-control" placeholder="اسم الشهادة" required></div>
                    <div class="col-12"><input name="issuing_organization" class="form-control" placeholder="الجهة المانحة"></div>
                    <div class="col-6"><input type="date" name="issued_at" class="form-control"></div>
                    <div class="col-6"><input type="url" name="credential_url" class="form-control" placeholder="رابط التحقق"></div>
                    <div class="col-12"><button class="btn btn-sm btn-primary rounded-pill px-3">إضافة شهادة</button></div>
                </form>
                @foreach($user->certifications as $certification)
                    <div class="item-row d-flex justify-content-between gap-2">
                        <div><div class="fw-semibold">{{ $certification->name }}</div><div class="text-muted small">{{ $certification->issuing_organization }}</div></div>
                        <form action="{{ route('user.profile.certifications.destroy', $certification) }}" method="POST">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">حذف</button></form>
                    </div>
                @endforeach
            </div>

            <div class="panel full-panel">
                <div class="panel-title">تغيير كلمة المرور</div>
                <form action="{{ route('user.profile.password') }}" method="POST" class="row g-3">
                    @csrf @method('PUT')
                    <div class="col-md-4"><input type="password" name="current_password" class="form-control" placeholder="كلمة المرور الحالية" required></div>
                    <div class="col-md-4"><input type="password" name="new_password" class="form-control" placeholder="كلمة المرور الجديدة" required></div>
                    <div class="col-md-4"><input type="password" name="new_password_confirmation" class="form-control" placeholder="تأكيد كلمة المرور" required></div>
                    <div class="col-12"><button class="btn btn-warning rounded-pill px-4">تغيير كلمة المرور</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
    reader.readAsDataURL(input.files[0]);
}
</script>
@endpush
@endsection
