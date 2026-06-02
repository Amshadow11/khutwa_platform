<?php

namespace App\Actions\Profile;

use App\Models\User;
use App\Models\UserProject;

class StoreProjectAction
{
    public function __construct(private readonly RefreshProfessionalProfileAction $refreshProfile)
    {
    }

    public function execute(User $user, array $data): UserProject
    {
        $project = $user->projects()->create($data);
        $this->refreshProfile->execute($user->fresh());

        return $project;
    }
}
