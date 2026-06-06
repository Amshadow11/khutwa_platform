<?php

namespace App\Services\Search;

use App\Data\Search\JobSearchFilters;
use App\Models\Job;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Laravel\Scout\Builder;

class JobSearchService
{
    public function search(JobSearchFilters $filters, int $perPage = 12): LengthAwarePaginator
    {
        return Job::search($filters->keyword ?? '')
            ->query(fn ($query) => $query->with('company:id,company_name,logo')->active())
            ->where('is_active', true)
            ->where('status', 'active')
            ->when($filters->location, fn (Builder $query, string $location) => $query->where('location', $location))
            ->when($filters->jobType, fn (Builder $query, string $jobType) => $query->where('job_type', $jobType))
            ->when($filters->category, fn (Builder $query, string $category) => $query->where('category', $category))
            ->when($filters->experienceLevel, fn (Builder $query, string $level) => $query->where('experience_level', $level))
            ->when($filters->remoteWork, fn (Builder $query) => $query->where('remote_work', true))
            ->when($filters->urgent, fn (Builder $query) => $query->where('urgent', true))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
