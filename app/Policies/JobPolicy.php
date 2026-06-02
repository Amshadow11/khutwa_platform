<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    public function view(?object $actor, Job $job): bool
    {
        if ($actor instanceof Company) {
            return $job->company_id === $actor->id;
        }

        return $job->is_active && $job->status === 'active';
    }

    public function create(Company $company): bool
    {
        return $company->is_verified && $company->status === 'active';
    }

    public function update(Company $company, Job $job): bool
    {
        return $this->ownsVerifiedCompanyJob($company, $job);
    }

    public function delete(Company $company, Job $job): bool
    {
        return $this->ownsVerifiedCompanyJob($company, $job);
    }

    public function toggle(Company $company, Job $job): bool
    {
        return $this->ownsVerifiedCompanyJob($company, $job);
    }

    public function apply(User $user, Job $job): bool
    {
        return $user->hasVerifiedEmail()
            && $user->is_active
            && $user->status === 'active'
            && $job->is_active
            && $job->status === 'active'
            && ! $job->is_expired;
    }

    private function ownsVerifiedCompanyJob(Company $company, Job $job): bool
    {
        return $job->company_id === $company->id
            && $company->is_verified
            && $company->status === 'active';
    }
}
