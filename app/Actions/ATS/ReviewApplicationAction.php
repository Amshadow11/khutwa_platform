<?php

namespace App\Actions\ATS;

use App\Models\Application;
use App\Models\ApplicationReview;
use App\Models\Company;
use App\Services\ATS\ApplicationEvaluationScoringService;

class ReviewApplicationAction
{
    public function __construct(
        private readonly LogApplicationActivityAction $activity,
        private readonly ApplicationEvaluationScoringService $scoring,
    ) {}

    public function execute(Application $application, Company $company, array $data): ApplicationReview
    {
        $evaluation = $this->scoring->build($application, $data);

        $review = ApplicationReview::query()->updateOrCreate(
            ['application_id' => $application->id, 'company_id' => $company->id],
            array_merge([
                'rating' => $data['rating'] ?? null,
                'recommendation' => $data['recommendation'] ?? null,
                'strengths' => $data['strengths'] ?? null,
                'concerns' => $data['concerns'] ?? null,
            ], $evaluation),
        );

        $this->activity->execute($application, $company, 'review_updated', 'تم تحديث تقييم المرشح.', [
            'rating' => $review->rating,
            'recommendation' => $review->recommendation,
            'overall_score' => $review->overall_score,
            'evaluated_snapshot_hash' => $review->evaluated_snapshot_hash,
        ]);

        return $review;
    }
}
