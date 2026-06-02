@extends('layouts.app')
@section('title', $seoTitle)
@section('description', $seoDescription)

@push('styles')
<link rel="canonical" href="{{ $canonicalUrl }}">
<meta property="og:type" content="profile">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
@if($resume->visibility === 'unlisted' || $resume->visibility === 'signed')
<meta name="robots" content="noindex,nofollow">
@endif
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex gap-2 justify-content-center flex-wrap no-print mb-3">
        @if($resume->generated_pdf_path)
            <a href="{{ $resume->downloadUrl() }}" class="btn btn-sm btn-primary rounded-pill">تحميل PDF</a>
        @endif
    </div>
    <div class="resume-render" lang="{{ $resume->locale }}" dir="{{ $resume->direction }}">
        {!! $renderedResume !!}
    </div>
</div>
@endsection
