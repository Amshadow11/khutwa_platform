<?php

namespace App\Actions\Matching;

use App\Models\Application;
use App\Models\Job;
use App\Services\Matching\JobMatchingScoringService;

class ScoreApplicationAgainstJobAction
{
    public function __construct(
        private readonly BuildJobMatchingInputAction $inputBuilder,
        private readonly JobMatchingScoringService $scoring,
        private readonly GenerateAIJobMatchExplanationAction $aiExplanation,
    ) {}

    public function execute(Job $job, Application $application, int $companyId): array
    {
        $input = $this->inputBuilder->execute($job, $application);
        $score = $this->scoring->score($input);

        return [
            'score' => $score,
            'ai_explanation' => $this->aiExplanation->execute($input, $score, $companyId),
        ];
    }
}
