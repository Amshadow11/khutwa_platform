<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Services\Resume\ResumeRenderer;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PublicResumeController extends Controller
{
    public function show(Resume $resume, ResumeRenderer $renderer): View
    {
        abort_if(! $resume->is_shareable, 404);
        abort_if($resume->visibility === 'signed' && ! request()->hasValidSignature(), 403);

        $resume->load(['user', 'template', 'sections.items']);
        $canonicalUrl = route('resumes.public.show', $resume->public_token);
        $seo = $resume->seo_metadata ?? [];

        return view('resumes.public.show', [
            'resume' => $resume,
            'renderedResume' => $renderer->view($resume),
            'canonicalUrl' => $canonicalUrl,
            'seoTitle' => $seo['title'] ?? $resume->title,
            'seoDescription' => $seo['description'] ?? '',
        ]);
    }

    public function download(Resume $resume)
    {
        abort_if(! $resume->is_shareable, 404);
        abort_if($resume->visibility === 'signed' && ! request()->hasValidSignature(), 403);
        abort_if(! $resume->generated_pdf_path, 404);

        return Storage::disk(config('resumes.disk', 'private'))->download(
            $resume->generated_pdf_path,
            str($resume->title)->slug()->append('.pdf')->toString()
        );
    }
}
