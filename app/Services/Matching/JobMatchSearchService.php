<?php

namespace App\Services\Matching;

use App\Data\Matching\JobMatchSearchFilters;
use App\Models\Company;
use App\Models\JobApplicationMatch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Laravel\Scout\Builder;

class JobMatchSearchService
{
    public function search(Company $company, JobMatchSearchFilters $filters, int $perPage = 20): LengthAwarePaginator
    {
        return JobApplicationMatch::search($filters->keyword ?? '')
            ->query(fn ($query) => $query->with([
                'application:id,job_id,user_id,applicant_name,resume_snapshot,status,applied_at',
                'application.user:id,username,full_name,profile_picture',
                'run:id,status,created_at,completed_at',
            ]))
            ->where('company_id', $company->id)
            ->where('status', JobApplicationMatch::STATUS_COMPLETED)
            ->when($filters->jobId, fn (Builder $query, int $jobId) => $query->where('job_id', $jobId))
            ->when($filters->runId, fn (Builder $query, int $runId) => $query->where('job_match_run_id', $runId))
            ->when($filters->reused !== null, fn (Builder $query) => $query->where('is_reused', $filters->reused))
            ->when($filters->minScore !== null, fn (Builder $query) => $query->where('overall_score', '>=', $filters->minScore))
            ->when($filters->maxScore !== null, fn (Builder $query) => $query->where('overall_score', '<=', $filters->maxScore))
            ->orderBy($filters->sort, $filters->direction)
            ->paginate($perPage)
            ->withQueryString();
    }
}
