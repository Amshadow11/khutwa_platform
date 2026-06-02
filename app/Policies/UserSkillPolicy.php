<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserSkill;

class UserSkillPolicy
{
    public function delete(User $user, UserSkill $skill): bool
    {
        return $user->is($skill->user);
    }
}
