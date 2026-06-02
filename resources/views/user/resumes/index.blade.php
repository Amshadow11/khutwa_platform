@extends('layouts.app')
@section('title', 'السير الذاتية')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1">السير الذاتية</h4>
            <div class="text-muted small">نسخ مستقلة قابلة للتخصيص والمشاركة والتوليد كـ PDF.</div>
        </div>
        <a href="{{ route('user.resumes.create') }}" class="btn btn-primary rounded-pill px-4">إنشاء سيرة</a>
    </div>

    <div class="row g-3">
        @forelse($resumes as $resume)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="fw-bold">{{ $resume->title }}</div>
                        <div class="text-muted small">{{ $resume->template?->name }} · v{{ $resume->snapshot_version }} · {{ $resume->visibility }}</div>
                        <div class="mt-3 d-flex gap-2 flex-wrap">
                            <a href="{{ route('user.resumes.show', $resume) }}" class="btn btn-sm btn-outline-primary rounded-pill">عرض</a>
                            <a href="{{ route('user.resumes.edit', $resume) }}" class="btn btn-sm btn-outline-secondary rounded-pill">إعدادات</a>
                            @if($resume->shareUrl())
                                <a href="{{ $resume->shareUrl() }}" class="btn btn-sm btn-outline-dark rounded-pill">رابط مشاركة</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-info">لم تنشئ أي سيرة بعد.</div></div>
        @endforelse
    </div>

    <div class="mt-3">{{ $resumes->links() }}</div>
</div>
@endsection
