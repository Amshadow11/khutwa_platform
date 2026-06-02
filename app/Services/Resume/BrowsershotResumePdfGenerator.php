<?php

namespace App\Services\Resume;

use App\Models\Resume;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\Browsershot\Browsershot;

class BrowsershotResumePdfGenerator
{
    public function __construct(private readonly ResumeRenderer $renderer)
    {
    }

    public function generate(Resume $resume): string
    {
        if (! class_exists(Browsershot::class)) {
            throw new RuntimeException('spatie/browsershot is not installed. Run composer install/update before generating resume PDFs.');
        }

        $html = $this->renderer->html($resume);
        $path = "resumes/{$resume->user_id}/{$resume->uuid}.pdf";
        $temporaryPath = storage_path("app/resume-{$resume->uuid}.pdf");

        $timeout = (int) config('resumes.pdf.timeout', 120);

        try {
            $browser = Browsershot::html($html)
                ->format(config('resumes.pdf.format', 'A4'))
                ->margins(
                    config('resumes.pdf.margin_top', 8),
                    config('resumes.pdf.margin_right', 8),
                    config('resumes.pdf.margin_bottom', 8),
                    config('resumes.pdf.margin_left', 8),
                )
                ->showBackground()
                ->newHeadless()
                ->noSandbox()
                ->setOption('waitUntil', 'load')
                ->setOption('protocolTimeout', $timeout * 120)
                ->timeout($timeout);

            if ($node = config('resumes.pdf.node_binary')) {
                $browser->setNodeBinary($node);
            }

            if ($npm = config('resumes.pdf.npm_binary')) {
                $browser->setNpmBinary($npm);
            }

            if ($chrome = config('resumes.pdf.chrome_path')) {
                $browser->setChromePath($chrome);
            }

            $browser->savePdf($temporaryPath);

            Storage::disk(config('resumes.disk', 'private'))->put($path, file_get_contents($temporaryPath));
        } finally {
            if (file_exists($temporaryPath)) {
                @unlink($temporaryPath);
            }
        }

        return $path;
    }
}
