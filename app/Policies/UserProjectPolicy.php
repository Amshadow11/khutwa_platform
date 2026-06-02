<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserProject;

class UserProjectPolicy
{
    public function update(User $user, UserProject $project): bool
    {
        return $user->is($project->user);
    }

    public function delete(User $user, UserProject $project): bool
    {
        return $user->is($project->user);
    }
}
