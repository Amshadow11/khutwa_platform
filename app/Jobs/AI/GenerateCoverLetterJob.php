<?php

namespace App\Jobs\AI;

use App\Actions\AI\GenerateCoverLetterAction;
use App\Models\Job;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class GenerateCoverLetterJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public string $queue = 'ai';

    public function __construct(
        public readonly string $requestId,
        public readonly int $userId,
        public readonly int $jobId,
        public readonly string $tone = 'professional',
    ) {}

    public function handle(GenerateCoverLetterAction $generateCoverLetter): void
    {
        $user = User::findOrFail($this->userId);
        $job = Job::with('company')->findOrFail($this->jobId);

        $coverLetter = $generateCoverLetter->execute($user, $job, $this->tone);

        Cache::put("ai:cover-letter:{$this->requestId}", [
            'status' => 'completed',
            'cover_letter' => $coverLetter,
            'job' => [
                'title' => $job->title,
                'company' => $job->company->company_name,
            ],
        ], now()->addDay());
    }

    public function failed(?\Throwable $exception): void
    {
        Cache::put("ai:cover-letter:{$this->requestId}", [
            'status' => 'failed',
            'message' => $exception?->getMessage() ?: 'AI generation failed.',
        ], now()->addDay());
    }

    public function tags(): array
    {
        return ['ai', 'cover-letter', 'user:' . $this->userId, 'job:' . $this->jobId];
    }
}
