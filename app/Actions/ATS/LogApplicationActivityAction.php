<?php

namespace App\Actions\ATS;

use App\Models\Application;
use App\Models\ApplicationActivity;
use App\Models\Company;

class LogApplicationActivityAction
{
    public function execute(Application $application, ?Company $company, string $type, string $description, array $metadata = []): ApplicationActivity
    {
        return $application->activities()->create([
            'company_id' => $company?->id,
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata ?: null,
            'occurred_at' => now(),
        ]);
    }
}
