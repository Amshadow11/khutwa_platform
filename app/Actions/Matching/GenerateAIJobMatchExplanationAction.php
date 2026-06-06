<?php

namespace App\Actions\Matching;

use App\Services\AI\AIService;
use App\Services\Matching\JobMatchingPromptBuilder;
use Illuminate\Support\Facades\Log;

class GenerateAIJobMatchExplanationAction
{
    public function __construct(
        private readonly AIService $ai,
        private readonly JobMatchingPromptBuilder $promptBuilder,
    ) {}

    public function execute(array $input, array $score, int $companyId): ?string
    {
        if (! config('ai.matching.enable_explanations', true)) {
            return null;
        }

        try {
            return $this->ai->executeWithSystem(
                feature: 'job_matching',
                system: $this->promptBuilder->system(),
                prompt: $this->promptBuilder->prompt($input, $score),
                options: ['temperature' => 0.2],
                companyId: $companyId,
            );
        } catch (\Throwable $e) {
            Log::warning('AI matching explanation failed; deterministic score was preserved.', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
