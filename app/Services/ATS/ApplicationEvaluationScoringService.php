<?php

namespace App\Services\ATS;

use App\Models\Application;

class ApplicationEvaluationScoringService
{
    private const RECOMMENDATION_WEIGHTS = [
        'strong_yes' => 8,
        'yes' => 4,
        'maybe' => 0,
        'no' => -6,
        'strong_no' => -12,
    ];

    public function build(Application $application, array $data): array
    {
        $rubricScores = $this->normalizeRubricScores($data['rubric_scores'] ?? []);
        $overallScore = $this->calculateOverallScore(
            $data['rating'] ?? null,
            $data['recommendation'] ?? null,
            $rubricScores
        );

        return [
            'overall_score' => $overallScore,
            'rubric_scores' => $rubricScores ?: null,
            'match_signals' => $this->buildMatchSignals($application, $rubricScores, $overallScore),
            'evaluated_snapshot_hash' => $application->resume_snapshot_hash,
            'evaluated_snapshot_version' => $application->resume_snapshot_version,
            'evaluated_at' => now(),
        ];
    }

    private function normalizeRubricScores(array $scores): array
    {
        return collect([
            'technical_fit',
            'experience_fit',
            'role_fit',
            'communication',
        ])
            ->mapWithKeys(function (string $key) use ($scores) {
                $value = $scores[$key] ?? null;

                return [$key => is_numeric($value) ? max(1, min(5, (int) $value)) : null];
            })
            ->filter(fn ($value) => $value !== null)
            ->all();
    }

    private function calculateOverallScore(mixed $rating, mixed $recommendation, array $rubricScores): ?float
    {
        $components = [];

        if (is_numeric($rating)) {
            $components[] = ((int) $rating) * 20;
        }

        if ($rubricScores !== []) {
            $components[] = (array_sum($rubricScores) / count($rubricScores)) * 20;
        }

        if ($components === []) {
            return null;
        }

        $score = array_sum($components) / count($components);
        $score += self::RECOMMENDATION_WEIGHTS[$recommendation] ?? 0;

        return round(max(0, min(100, $score)), 2);
    }

    private function buildMatchSignals(Application $application, array $rubricScores, ?float $overallScore): array
    {
        $application->loadMissing('job:id,title,description,requirements');

        $snapshot = $application->resume_snapshot ?? [];
        $skills = collect($snapshot['skills'] ?? [])
            ->pluck('name')
            ->filter()
            ->values();

        $jobText = mb_strtolower(collect([
            $application->job?->title,
            $application->job?->description,
            $application->job?->requirements,
        ])->filter()->implode(' '));

        $matchedSkills = $skills
            ->filter(fn (string $skill) => $skill !== '' && str_contains($jobText, mb_strtolower($skill)))
            ->values()
            ->all();

        return [
            'schema' => 1,
            'source' => 'manual_review',
            'snapshot_hash' => $application->resume_snapshot_hash,
            'snapshot_version' => $application->resume_snapshot_version,
            'overall_score' => $overallScore,
            'rubric_scores' => $rubricScores,
            'candidate_skill_count' => $skills->count(),
            'matched_skills' => $matchedSkills,
            'matched_skill_count' => count($matchedSkills),
            'generated_at' => now()->toISOString(),
        ];
    }
}
