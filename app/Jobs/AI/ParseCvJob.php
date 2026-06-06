<?php

namespace App\Jobs\AI;

use App\Actions\AI\ParseCVAction;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ParseCvJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public string $queue = 'ai';

    public function __construct(
        public readonly int $userId,
        public readonly string $cvPath,
        public readonly bool $updateProfile = false,
        public readonly ?int $applicationId = null,
    ) {}

    public function handle(ParseCVAction $parseCVAction): void
    {
        $user = User::findOrFail($this->userId);

        $parseCVAction->execute(
            user: $user,
            cvPath: $this->cvPath,
            updateProfile: $this->updateProfile,
            applicationId: $this->applicationId,
        );
    }

    public function tags(): array
    {
        return array_filter([
            'ai',
            'cv-parser',
            'user:' . $this->userId,
            $this->applicationId ? 'application:' . $this->applicationId : null,
        ]);
    }
}
