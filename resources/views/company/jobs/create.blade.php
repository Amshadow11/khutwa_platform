@extends('layouts.company')

@section('title', isset($job) ? 'تعديل الوظيفة' : 'نشر وظيفة جديدة')
@section('page-title', isset($job) ? 'تعديل: ' . $job->title : 'نشر وظيفة جديدة')

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-xl-9">

        <form action="{{ isset($job) ? route('company.jobs.update', $job) : route('company.jobs.store') }}"
              method="POST">
            @csrf
            @if(isset($job))
                @method('PUT')
            @endif

            {{-- ===== القسم 1: المعلومات الأساسية ===== --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2 text-primary"></i>
                    المعلومات الأساسية
                </div>
                <div class="card-body">

                    {{-- عنوان الوظيفة --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            عنوان الوظيفة <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="title"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $job->title ?? '') }}"
                               placeholder="مثال: مهندس برمجيات، محاسب، مصمم جرافيك"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- نوع الإعلان ونوع الدوام --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                نوع الإعلان <span class="text-danger">*</span>
                            </label>
                            <select name="category"
                                    class="form-select @error('category') is-invalid @enderror" required>
                                <option value="job" {{ old('category', $job->category ?? 'job') === 'job' ? 'selected' : '' }}>
                                    وظيفة
                                </option>
                                <option value="training" {{ old('category', $job->category ?? '') === 'training' ? 'selected' : '' }}>
                                    تدريب
                                </option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                نوع الدوام <span class="text-danger">*</span>
                            </label>
                            <select name="job_type"
                                    class="form-select @error('job_type') is-invalid @enderror" required>
                                @foreach([
                                    'full_time' => 'دوام كامل',
                                    'part_time' => 'دوام جزئي',
                                    'remote'    => 'عمل عن بُعد',
                                    'contract'  => 'عقد مؤقت',
                                    'freelance' => 'عمل حر',
                                ] as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('job_type', $job->job_type ?? '') === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('job_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">مستوى الخبرة</label>
                            <select name="experience_level"
                                    class="form-select @error('experience_level') is-invalid @enderror">
                                <option value="">غير محدد</option>
                                @foreach([
                                    'junior'  => 'مبتدئ (0-2 سنة)',
                                    'mid'     => 'متوسط (2-5 سنوات)',
                                    'senior'  => 'خبير (+5 سنوات)',
                                    'manager' => 'مدير / قيادي',
                                ] as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('experience_level', $job->experience_level ?? '') === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- الموقع + الراتب --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                موقع العمل <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="location"
                                   class="form-control @error('location') is-invalid @enderror"
                                   value="{{ old('location', $job->location ?? '') }}"
                                   placeholder="مثال: صنعاء، عدن، تعز"
                                   required>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الراتب (اختياري)</label>
                            <input type="text"
                                   name="salary"
                                   class="form-control @error('salary') is-invalid @enderror"
                                   value="{{ old('salary', $job->salary ?? '') }}"
                                   placeholder="مثال: 150,000 ريال أو حسب الخبرة">
                        </div>
                    </div>

                    {{-- خيارات إضافية --}}
                    <div class="d-flex gap-4 flex-wrap">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   name="remote_work" id="remoteWork"
                                   {{ old('remote_work', $job->remote_work ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="remoteWork">
                                يقبل العمل عن بُعد
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   name="urgent" id="urgentJob"
                                   {{ old('urgent', $job->urgent ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="urgentJob">
                                <span class="text-danger">⚡</span> وظيفة عاجلة
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ===== القسم 2: الوصف والمتطلبات ===== --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fas fa-file-alt me-2 text-primary"></i>
                    تفاصيل الوظيفة
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            وصف الوظيفة <span class="text-danger">*</span>
                        </label>
                        <textarea name="description"
                                  rows="5"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="اكتب وصفاً واضحاً للوظيفة، المهام، والمسؤوليات..."
                                  required>{{ old('description', $job->description ?? '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">على الأقل 30 حرف. وصف واضح يجذب أفضل المتقدمين.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">المتطلبات والمؤهلات</label>
                        <textarea name="requirements"
                                  rows="4"
                                  class="form-control @error('requirements') is-invalid @enderror"
                                  placeholder="المؤهلات المطلوبة، المهارات، سنوات الخبرة...">{{ old('requirements', $job->requirements ?? '') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">المزايا والمكافآت</label>
                        <textarea name="benefits"
                                  rows="3"
                                  class="form-control @error('benefits') is-invalid @enderror"
                                  placeholder="راتب تنافسي، تأمين صحي، إجازات...">{{ old('benefits', $job->benefits ?? '') }}</textarea>
                    </div>

                    {{-- تاريخ انتهاء التقديم --}}
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">آخر موعد للتقديم</label>
                        <input type="date"
                               name="deadline"
                               class="form-control @error('deadline') is-invalid @enderror"
                               value="{{ old('deadline', isset($job) && $job->deadline ? $job->deadline->format('Y-m-d') : '') }}"
                               min="{{ now()->addDay()->toDateString() }}">
                        @error('deadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- ===== أزرار الحفظ ===== --}}
            <div class="card">
                <div class="card-body d-flex gap-2 justify-content-end flex-wrap">
                    <a href="{{ route('company.jobs.index') }}"
                       class="btn btn-outline-secondary rounded-pill px-4">
                        إلغاء
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-{{ isset($job) ? 'save' : 'plus' }} me-1"></i>
                        {{ isset($job) ? 'حفظ التعديلات' : 'نشر الوظيفة' }}
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

@endsection
