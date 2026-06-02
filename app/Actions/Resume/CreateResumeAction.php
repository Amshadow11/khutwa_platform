<?php

namespace App\Actions\Resume;

use App\Models\Resume;
use App\Models\User;
use App\Services\Resume\ResumeCompositionService;

class CreateResumeAction
{
    public function __construct(private readonly ResumeCompositionService $composition)
    {
    }

    public function execute(User $user, array $data): Resume
    {
        return $this->composition->createFromProfile($user, $data);
    }
}
