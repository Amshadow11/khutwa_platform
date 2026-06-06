<?php

namespace App\Actions\Matching;

use App\Data\Matching\JobMatchSearchFilters;
use App\Models\Company;
use App\Services\Matching\JobMatchSearchService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchJobApplicationMatchesAction
{
    public function __construct(private readonly JobMatchSearchService $search) {}

    public function execute(Company $company, JobMatchSearchFilters $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search->search($company, $filters, $perPage);
    }
}
