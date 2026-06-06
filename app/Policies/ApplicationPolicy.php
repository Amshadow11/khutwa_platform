<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\Company;
use App\Models\User;

class ApplicationPolicy
{
    public function view(object $actor, Application $application): bool
    {
        if ($actor instanceof User) {
            return $application->user_id === $actor->id;
        }

        if ($actor instanceof Company) {
            return (int) $application->job?->company_id === (int) $actor->id;
        }

        return false;
    }

    public function withdraw(User $user, Application $application): bool
    {
        return $application->user_id === $user->id
            && in_array($application->status, [
                Application::STATUS_PENDING,
                Application::STATUS_VIEWED,
            ], true);
    }

    public function transitionStatus(Company $company, Application $application): bool
    {
        return (int) $application->job?->company_id === (int) $company->id
            && $company->is_verified
            && $company->status === 'active';
    }

    public function addNote(Company $company, Application $application): bool
    {
        return $this->transitionStatus($company, $application);
    }

    public function review(Company $company, Application $application): bool
    {
        return $this->transitionStatus($company, $application);
    }

    public function scheduleInterview(Company $company, Application $application): bool
    {
        return $this->transitionStatus($company, $application);
    }

    public function viewTimeline(object $actor, Application $application): bool
    {
        return $this->view($actor, $application);
    }

    public function viewMatch(Company $company, Application $application): bool
    {
        return $this->transitionStatus($company, $application);
    }

    public function downloadCv(object $actor, Application $application): bool
    {
        return $this->view($actor, $application);
    }

    public function downloadSubmittedResume(object $actor, Application $application): bool
    {
        return $this->downloadCv($actor, $application);
    }
}
