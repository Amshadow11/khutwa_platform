<?php

namespace App\Actions\Profile;

use App\Models\UserCertification;

class UpdateCertificationAction
{
    public function __construct(private readonly RefreshProfessionalProfileAction $refreshProfile)
    {
    }

    public function execute(UserCertification $certification, array $data): UserCertification
    {
        $certification->update($data);
        $this->refreshProfile->execute($certification->user()->first());

        return $certification->fresh();
    }
}
