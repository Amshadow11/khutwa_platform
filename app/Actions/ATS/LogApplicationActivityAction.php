<?php

namespace App\Actions\ATS;

use App\Models\Application;
use App\Models\ApplicationActivity;
use App\Models\Company;

class LogApplicationActivityAction
{
    public function execute(
        Application $application,
        ?Company $company,
        string $type,
        string $description,
        array $metadata = [],
        ?object $actor = null,
    ): ApplicationActivity
    {
        $actor ??= $company;

        return $application->activities()->create([
            'actor_type' => $actor ? $actor::class : null,
            'actor_id' => method_exists($actor, 'getKey') ? $actor->getKey() : null,
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata ?: null,
            'occurred_at' => now(),
        ]);
    }
}
