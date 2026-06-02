<?php

namespace App\Actions\Search;

use App\Models\Job;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchJobsAction
{
    public function execute(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return Job::with('company:id,company_name,logo')
            ->active()
            ->filter($filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
