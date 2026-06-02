<?php

namespace App\Actions\Profile;

use App\Models\UserExperience;

class UpdateExperienceAction
{
    public function __construct(private readonly RefreshProfessionalProfileAction $refreshProfile)
    {
    }

    public function execute(UserExperience $experience, array $data): UserExperience
    {
        $experience->update($data);
        $this->refreshProfile->execute($experience->user()->first());

        return $experience->fresh();
    }
}
