<?php

namespace App\Policies;

use App\Models\Resume;
use App\Models\User;

class ResumePolicy
{
    public function view(?User $user, Resume $resume): bool
    {
        return $resume->is_shareable || ($user && $resume->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail() && $user->is_active;
    }

    public function update(User $user, Resume $resume): bool
    {
        return $resume->user_id === $user->id;
    }

    public function delete(User $user, Resume $resume): bool
    {
        return $resume->user_id === $user->id;
    }

    public function generatePdf(User $user, Resume $resume): bool
    {
        return $resume->user_id === $user->id;
    }
}
