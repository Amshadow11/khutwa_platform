<?php

namespace App\Events;

use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusTransitioned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Application $application,
        public readonly ApplicationStatusHistory $history,
        public readonly ?string $fromStatus,
        public readonly string $toStatus,
        public readonly ?object $actor,
        public readonly string $transitionKey,
        public readonly ?string $note = null,
        public readonly bool $notifyCandidate = true,
    ) {}
}
