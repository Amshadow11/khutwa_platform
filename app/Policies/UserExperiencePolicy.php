<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserExperience;

class UserExperiencePolicy
{
    public function update(User $user, UserExperience $experience): bool
    {
        return $user->is($experience->user);
    }

    public function delete(User $user, UserExperience $experience): bool
    {
        return $user->is($experience->user);
    }
}
