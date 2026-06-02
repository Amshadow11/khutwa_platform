<?php

namespace App\Actions\Profile;

use App\Models\Language;
use App\Models\User;

class UpsertUserLanguageAction
{
    public function __construct(private readonly RefreshProfessionalProfileAction $refreshProfile)
    {
    }

    public function execute(User $user, array $data): Language
    {
        $language = Language::query()->findOrFail($data['language_id']);

        $user->languages()->syncWithoutDetaching([
            $language->id => [
                'proficiency_level' => $data['proficiency_level'] ?? null,
                'proficiency_score' => $data['proficiency_score'] ?? null,
                'is_native' => (bool) ($data['is_native'] ?? false),
                'sort_order' => $data['sort_order'] ?? 0,
            ],
        ]);

        $this->refreshProfile->execute($user->fresh());

        return $language;
    }
}
