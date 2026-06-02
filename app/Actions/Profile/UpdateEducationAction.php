<?php

namespace App\Actions\Profile;

use App\Models\UserEducation;

class UpdateEducationAction
{
    public function __construct(private readonly RefreshProfessionalProfileAction $refreshProfile)
    {
    }

    public function execute(UserEducation $education, array $data): UserEducation
    {
        $education->update($data);
        $this->refreshProfile->execute($education->user()->first());

        return $education->fresh();
    }
}
