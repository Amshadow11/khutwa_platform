<?php

namespace App\Actions\Profile;

use App\Models\User;
use App\Models\UserEducation;

class StoreEducationAction
{
    public function __construct(private readonly RefreshProfessionalProfileAction $refreshProfile)
    {
    }

    public function execute(User $user, array $data): UserEducation
    {
        $education = $user->educations()->create($data + ['source' => 'manual']);
        $this->refreshProfile->execute($user->fresh());

        return $education;
    }
}
