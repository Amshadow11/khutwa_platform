<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\Process\Process;

class ResumeDoctor extends Command
{
    protected $signature = 'resume:doctor';

    protected $description = 'Check Resume rendering and PDF generation environment.';

    public function handle(): int
    {
        $this->info('Resume system diagnostics');

        $this->line('Default template: ' . config('resumes.default_template'));
        $this->line('Storage disk: ' . config('resumes.disk'));
        $this->line('PDF queue: ' . config('resumes.pdf_queue'));

        $this->status('Browsershot package', class_exists(Browsershot::class));
        $this->status('Resume disk writable', $this->diskWritable());
        $this->status('Node binary', $this->commandWorks(config('resumes.pdf.node_binary') ?: 'node', ['--version']));
        $this->status('NPM binary', $this->commandWorks(config('resumes.pdf.npm_binary') ?: 'npm', ['--version']));

        $chromePath = config('resumes.pdf.chrome_path');
        if ($chromePath) {
            $this->status('Configured Chrome path exists', file_exists($chromePath));
        } else {
            $this->warn('Chrome path is not configured. Browsershot may still work if Chromium is bundled with Puppeteer.');
        }

        return self::SUCCESS;
    }

    private function status(string $label, bool $ok): void
    {
        $this->line(sprintf('%s: %s', $label, $ok ? '<info>OK</info>' : '<error>FAIL</error>'));
    }

    private function diskWritable(): bool
    {
        try {
            $path = 'resumes/doctor/.write-test';
            Storage::disk(config('resumes.disk', 'private'))->put($path, 'ok');
            Storage::disk(config('resumes.disk', 'private'))->delete($path);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function commandWorks(string $binary, array $arguments): bool
    {
        try {
            $process = new Process([$binary, ...$arguments]);
            $process->setTimeout(10);
            $process->run();

            return $process->isSuccessful();
        } catch (\Throwable) {
            return false;
        }
    }
}
