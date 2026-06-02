<?php

namespace App\Actions\Profile;

use App\Models\UserProject;

class UpdateProjectAction
{
    public function __construct(private readonly RefreshProfessionalProfileAction $refreshProfile)
    {
    }

    public function execute(UserProject $project, array $data): UserProject
    {
        $project->update($data);
        $this->refreshProfile->execute($project->user()->first());

        return $project->fresh();
    }
}
