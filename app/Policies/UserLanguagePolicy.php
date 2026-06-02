<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserLanguage;

class UserLanguagePolicy
{
    public function delete(User $user, UserLanguage $language): bool
    {
        return $user->is($language->user);
    }
}
