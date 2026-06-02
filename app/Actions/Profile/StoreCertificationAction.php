<?php

namespace App\Actions\Profile;

use App\Models\User;
use App\Models\UserCertification;

class StoreCertificationAction
{
    public function __construct(private readonly RefreshProfessionalProfileAction $refreshProfile)
    {
    }

    public function execute(User $user, array $data): UserCertification
    {
        $certification = $user->certifications()->create($data);
        $this->refreshProfile->execute($user->fresh());

        return $certification;
    }
}
