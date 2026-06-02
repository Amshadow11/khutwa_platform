<?php

namespace App\Actions\Profile;

use App\Models\User;
use App\Services\Profile\ProfileCompletenessService;
use App\Services\Profile\ProfileSearchDocumentBuilder;

class RefreshProfessionalProfileAction
{
    public function __construct(
        private readonly ProfileCompletenessService $completeness,
        private readonly ProfileSearchDocumentBuilder $searchDocumentBuilder,
    ) {
    }

    public function execute(User $user): array
    {
        $result = $this->completeness->refresh($user);
        $profile = $user->professionalProfile()->firstOrCreate([]);

        $profile->forceFill([
            'search_document' => $this->searchDocumentBuilder->build($user),
            'last_indexed_at' => now(),
        ])->save();

        return $result;
    }
}
