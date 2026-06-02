<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserCertification;

class UserCertificationPolicy
{
    public function update(User $user, UserCertification $certification): bool
    {
        return $user->is($certification->user);
    }

    public function delete(User $user, UserCertification $certification): bool
    {
        return $user->is($certification->user);
    }
}
