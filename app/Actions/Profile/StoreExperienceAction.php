<?php

namespace App\Actions\Profile;

use App\Models\User;
use App\Models\UserExperience;

class StoreExperienceAction
{
    public function __construct(private readonly RefreshProfessionalProfileAction $refreshProfile)
    {
    }

    public function execute(User $user, array $data): UserExperience
    {
        $experience = $user->experiences()->create($data + ['source' => 'manual']);
        $this->refreshProfile->execute($user->fresh());

        return $experience;
    }
}
