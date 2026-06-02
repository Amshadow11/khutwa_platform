<?php

namespace App\Jobs\Resume;

use App\Actions\Resume\GenerateResumePdfAction;
use App\Models\Resume;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateResumePdfJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(public int $resumeId)
    {
        $this->onQueue(config('resumes.pdf_queue', 'pdf'));
    }

    public function handle(GenerateResumePdfAction $action): void
    {
        $resume = Resume::query()->with(['user', 'template', 'sections.items'])->findOrFail($this->resumeId);
        $action->execute($resume);
    }

    public function failed(Throwable $exception): void
    {
        Resume::query()
            ->whereKey($this->resumeId)
            ->update([
                'pdf_status' => 'failed',
                'pdf_error' => str($exception->getMessage())->limit(2000)->toString(),
            ]);
    }
}
