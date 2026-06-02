@extends('layouts.app')
@section('title', $resume->title)

@section('content')
@php
    $pdfIsPending = in_array($resume->pdf_status, ['queued', 'processing'], true);
@endphp

<div class="container-fluid py-3">
    <div class="d-flex gap-2 justify-content-center flex-wrap no-print mb-3">
        <a href="{{ route('user.resumes.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">كل السير</a>
        <a href="{{ route('user.resumes.edit', $resume) }}" class="btn btn-sm btn-outline-primary rounded-pill">إعدادات</a>
        <form action="{{ route('user.resumes.refresh-snapshot', $resume) }}" method="POST">
            @csrf
            <button class="btn btn-sm btn-outline-dark rounded-pill">تحديث Snapshot</button>
        </form>
        <form action="{{ route('user.resumes.generate-pdf', $resume) }}" method="POST">
            @csrf
            <button class="btn btn-sm btn-primary rounded-pill" @disabled($pdfIsPending)>
                {{ $pdfIsPending ? 'PDF قيد التوليد' : 'توليد PDF' }}
            </button>
        </form>
        @if($resume->generated_pdf_path)
            <a href="{{ route('user.resumes.download', $resume) }}" class="btn btn-sm btn-success rounded-pill">تحميل PDF</a>
        @endif
        @if($resume->shareUrl())
            <a href="{{ $resume->shareUrl() }}" class="btn btn-sm btn-outline-success rounded-pill">رابط المشاركة</a>
        @endif
    </div>

    @if($pdfIsPending)
        <div class="container mb-3 no-print">
            <div class="alert alert-info mb-0">
                يتم توليد ملف PDF الآن. سيتم تحديث الصفحة تلقائيًا عند اكتمال التوليد.
            </div>
        </div>
    @elseif($resume->pdf_status === 'generated' && $resume->generated_pdf_path)
        <div class="container mb-3 no-print">
            <div class="alert alert-success mb-0">
                تم توليد ملف PDF بنجاح. يمكنك تحميله الآن.
            </div>
        </div>
    @endif

    @if($resume->pdf_error)
        <div class="container mb-3 no-print">
            <div class="alert alert-warning mb-0">
                <strong>تعذر توليد PDF:</strong>
                <span class="small">{{ $resume->pdf_error }}</span>
            </div>
        </div>
    @endif

    <div class="resume-render" lang="{{ $resume->locale }}" dir="{{ $resume->direction }}">
        {!! $renderedResume !!}
    </div>
</div>
@endsection

@if($pdfIsPending)
    @push('scripts')
        <script>
            setTimeout(() => window.location.reload(), 3000);
        </script>
    @endpush
@endif
