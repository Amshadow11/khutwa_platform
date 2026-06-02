<?php

namespace App\Actions\Profile;

use App\Models\ProfessionalProfile;
use App\Models\User;

class UpdateProfessionalProfileAction
{
    public function __construct(private readonly RefreshProfessionalProfileAction $refreshProfile)
    {
    }

    public function execute(User $user, array $data): ProfessionalProfile
    {
        $profile = $user->professionalProfile()->firstOrCreate([]);
        $profile->fill($data);
        $profile->save();

        $this->refreshProfile->execute($user->fresh());

        return $profile->fresh();
    }
}
