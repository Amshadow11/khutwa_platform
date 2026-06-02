<?php

namespace App\Actions\Resume;

use App\Models\Resume;
use App\Services\Resume\BrowsershotResumePdfGenerator;

class GenerateResumePdfAction
{
    public function __construct(private readonly BrowsershotResumePdfGenerator $pdfGenerator)
    {
    }

    public function execute(Resume $resume): Resume
    {
        $resume->forceFill([
            'pdf_status' => 'processing',
            'pdf_error' => null,
        ])->save();

        $path = $this->pdfGenerator->generate($resume);

        $resume->forceFill([
            'generated_pdf_path' => $path,
            'pdf_status' => 'generated',
            'pdf_error' => null,
            'last_generated_at' => now(),
            'render_metadata' => [
                'driver' => 'browsershot',
                'format' => config('resumes.pdf.format', 'A4'),
                'generated_at' => now()->toISOString(),
            ],
        ])->save();

        return $resume->fresh();
    }
}
