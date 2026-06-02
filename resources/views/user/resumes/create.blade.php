@extends('layouts.app')
@section('title', 'إنشاء سيرة ذاتية')

@section('content')
<div class="container py-4" style="max-width: 760px">
    <h4 class="fw-bold mb-3">إنشاء سيرة ذاتية</h4>
    <form action="{{ route('user.resumes.store') }}" method="POST" class="card">
        @csrf
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">العنوان</label>
                <input name="title" class="form-control" value="{{ old('title', 'Professional Resume') }}" required>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">القالب</label>
                    <select name="template_slug" class="form-select" required>
                        @foreach($templates as $template)
                            <option value="{{ $template->slug }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">الظهور</label>
                    <select name="visibility" class="form-select">
                        <option value="private">خاص</option>
                        <option value="public">عام ومفهرس</option>
                        <option value="unlisted">برابط فقط</option>
                        <option value="signed">رابط موقّع</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">اللغة</label>
                    <input name="locale" class="form-control" value="ar" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">الاتجاه</label>
                    <select name="direction" class="form-select">
                        <option value="rtl">RTL</option>
                        <option value="ltr">LTR</option>
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label">ملخص مخصص لهذه النسخة</label>
                <textarea name="tailored_summary" rows="4" class="form-control">{{ old('tailored_summary') }}</textarea>
            </div>
            <button class="btn btn-primary rounded-pill px-4 mt-3">إنشاء</button>
        </div>
    </form>
</div>
@endsection
