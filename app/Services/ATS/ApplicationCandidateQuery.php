<?php

namespace App\Services\ATS;

use App\Models\Application;
use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;

class ApplicationCandidateQuery
{
    public function forCompany(Company $company, array $filters = []): Builder
    {
        $jobIds = $company->jobs()
            ->when($filters['job_id'] ?? null, fn (Builder $query, mixed $jobId) => $query->whereKey($jobId))
            ->pluck('id');

        $query = Application::query()
            ->with([
                'user:id,username,full_name,email,phone,profile_picture',
                'job:id,title,location,company_id',
                'resume:id,title,version_number',
                'reviews',
                'interviews',
            ])
            ->withMax('reviews as ats_score', 'overall_score')
            ->withCount('interviews as interviews_count')
            ->whereIn('job_id', $jobIds);

        $this->applyFilters($query, $filters);
        $this->applyOrdering($query, $filters['sort'] ?? null);

        return $query;
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['recommendation'] ?? null, fn (Builder $query, string $recommendation) => $query
                ->whereHas('reviews', fn (Builder $review) => $review->where('recommendation', $recommendation)))
            ->when($filters['min_score'] ?? null, fn (Builder $query, mixed $score) => $query
                ->whereHas('reviews', fn (Builder $review) => $review->where('overall_score', '>=', (float) $score)))
            ->when(($filters['has_interview'] ?? '') !== '', function (Builder $query) use ($filters) {
                $hasInterview = filter_var($filters['has_interview'], FILTER_VALIDATE_BOOLEAN);

                $hasInterview
                    ? $query->whereHas('interviews')
                    : $query->whereDoesntHave('interviews');
            })
            ->when($filters['q'] ?? null, fn (Builder $query, string $term) => $this->applySearch($query, $term));
    }

    private function applySearch(Builder $query, string $term): void
    {
        $term = trim($term);

        if ($term === '') {
            return;
        }

        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';

        $query->where(function (Builder $query) use ($like) {
            $query
                ->where('applicant_name', 'like', $like)
                ->orWhere('applicant_email', 'like', $like)
                ->orWhere('applicant_phone', 'like', $like)
                ->orWhere('resume_snapshot', 'like', $like);
        });
    }

    private function applyOrdering(Builder $query, ?string $sort): void
    {
        match ($sort) {
            'score_desc' => $query->orderByDesc('ats_score')->latest('applied_at'),
            'score_asc' => $query->orderBy('ats_score')->latest('applied_at'),
            'oldest' => $query->oldest('applied_at'),
            default => $query->latest('applied_at'),
        };
    }
}
