<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserEducation;

class UserEducationPolicy
{
    public function update(User $user, UserEducation $education): bool
    {
        return $user->is($education->user);
    }

    public function delete(User $user, UserEducation $education): bool
    {
        return $user->is($education->user);
    }
}
