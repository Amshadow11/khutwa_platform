<?php

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DeleteProfessionalItemAction
{
    public function __construct(private readonly RefreshProfessionalProfileAction $refreshProfile)
    {
    }

    public function execute(User $user, Model $model): void
    {
        $model->delete();
        $this->refreshProfile->execute($user->fresh());
    }
}
