<?php

namespace App\Services\Matching;

use Illuminate\Support\Str;

class JobMatchingScoringService
{
    public function score(array $input): array
    {
        $jobText = $this->normalizeText(collect([
            $input['job']['title'] ?? null,
            $input['job']['description'] ?? null,
            $input['job']['requirements'] ?? null,
        ])->filter()->implode(' '));

        $candidate = $input['candidate'] ?? [];
        $candidateSkills = $this->extractNames($candidate['skills'] ?? []);
        $jobSkills = $this->extractJobSkillHints($jobText);
        $matchedSkills = $this->matchTerms($candidateSkills, $jobText);
        $missingSkills = array_values(array_diff($jobSkills, $matchedSkills));

        $skillsScore = $candidateSkills === []
            ? 0.0
            : min(100, (count($matchedSkills) / max(1, min(count($candidateSkills), max(1, count($jobSkills))))) * 100);

        $experienceScore = $this->scoreExperience($candidate['experiences'] ?? [], $jobText);
        $educationScore = $this->scoreEducation($candidate['educations'] ?? [], $jobText);
        $locationScore = $this->scoreLocation($candidate['identity'] ?? [], $input['job'] ?? []);
        $seniorityScore = $this->scoreSeniority($candidate['identity'] ?? [], $candidate['experiences'] ?? [], $input['job']['experience_level'] ?? null);

        $overall = round(
            ($skillsScore * 0.40)
            + ($experienceScore * 0.25)
            + ($educationScore * 0.10)
            + ($locationScore * 0.10)
            + ($seniorityScore * 0.15),
            2
        );

        return [
            'overall_score' => $overall,
            'skills_score' => round($skillsScore, 2),
            'experience_score' => round($experienceScore, 2),
            'education_score' => round($educationScore, 2),
            'location_score' => round($locationScore, 2),
            'seniority_score' => round($seniorityScore, 2),
            'matched_skills' => $matchedSkills,
            'missing_skills' => array_slice($missingSkills, 0, 15),
            'evidence' => [
                'candidate_skill_count' => count($candidateSkills),
                'job_skill_hint_count' => count($jobSkills),
                'experience_count' => count($candidate['experiences'] ?? []),
                'education_count' => count($candidate['educations'] ?? []),
                'source' => 'deterministic_snapshot_score',
            ],
            'risk_flags' => $this->riskFlags($candidateSkills, $matchedSkills, $candidate['experiences'] ?? []),
        ];
    }

    private function extractNames(array $items): array
    {
        return collect($items)
            ->map(fn ($item) => is_array($item) ? ($item['name'] ?? $item['title'] ?? null) : $item)
            ->filter()
            ->map(fn (string $value) => trim($value))
            ->filter()
            ->unique(fn (string $value) => mb_strtolower($value))
            ->values()
            ->all();
    }

    private function extractJobSkillHints(string $jobText): array
    {
        $known = [
            'laravel', 'php', 'mysql', 'redis', 'vue', 'react', 'javascript', 'typescript',
            'api', 'rest', 'aws', 'docker', 'kubernetes', 'python', 'java', 'node',
            'figma', 'seo', 'sales', 'marketing', 'project management', 'product management',
        ];

        return collect($known)
            ->filter(fn (string $skill) => str_contains($jobText, mb_strtolower($skill)))
            ->values()
            ->all();
    }

    private function matchTerms(array $candidateTerms, string $jobText): array
    {
        return collect($candidateTerms)
            ->filter(fn (string $term) => $term !== '' && str_contains($jobText, mb_strtolower($term)))
            ->values()
            ->all();
    }

    private function scoreExperience(array $experiences, string $jobText): float
    {
        if ($experiences === []) {
            return 20.0;
        }

        $titles = collect($experiences)
            ->map(fn ($experience) => is_array($experience) ? ($experience['title'] ?? $experience['role'] ?? null) : null)
            ->filter()
            ->map(fn (string $title) => $this->normalizeText($title));

        $matched = $titles->filter(fn (string $title) => Str::of($title)->explode(' ')
            ->filter(fn (string $word) => mb_strlen($word) > 3)
            ->contains(fn (string $word) => str_contains($jobText, $word)))
            ->count();

        return min(100, 55 + ($matched * 20) + min(25, count($experiences) * 5));
    }

    private function scoreEducation(array $educations, string $jobText): float
    {
        if ($educations === []) {
            return 50.0;
        }

        $educationText = $this->normalizeText(json_encode($educations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        if (str_contains($jobText, 'degree') || str_contains($jobText, 'bachelor') || str_contains($jobText, 'master')) {
            return str_contains($educationText, 'bachelor') || str_contains($educationText, 'master') || str_contains($educationText, 'degree')
                ? 90.0
                : 65.0;
        }

        return 75.0;
    }

    private function scoreLocation(array $identity, array $job): float
    {
        if (($job['remote_work'] ?? false) === true) {
            return 100.0;
        }

        $candidateLocation = $this->normalizeText(collect([
            $identity['city'] ?? null,
            $identity['country'] ?? null,
            $identity['location'] ?? null,
        ])->filter()->implode(' '));

        $jobLocation = $this->normalizeText((string) ($job['location'] ?? ''));

        if ($candidateLocation === '' || $jobLocation === '') {
            return 60.0;
        }

        return str_contains($candidateLocation, $jobLocation) || str_contains($jobLocation, $candidateLocation) ? 100.0 : 45.0;
    }

    private function scoreSeniority(array $identity, array $experiences, ?string $experienceLevel): float
    {
        $headline = $this->normalizeText(collect([
            $identity['headline'] ?? null,
            $identity['current_title'] ?? null,
        ])->filter()->implode(' '));

        $experienceCount = count($experiences);
        $level = $this->normalizeText((string) $experienceLevel);

        if ($level === '') {
            return min(100, 55 + ($experienceCount * 10));
        }

        if (str_contains($level, 'senior') || str_contains($level, 'expert')) {
            return str_contains($headline, 'senior') || $experienceCount >= 3 ? 90.0 : 55.0;
        }

        if (str_contains($level, 'junior') || str_contains($level, 'entry')) {
            return $experienceCount <= 2 ? 85.0 : 70.0;
        }

        return min(100, 60 + ($experienceCount * 8));
    }

    private function riskFlags(array $candidateSkills, array $matchedSkills, array $experiences): array
    {
        $flags = [];

        if ($candidateSkills === []) {
            $flags[] = 'no_structured_skills';
        }

        if ($matchedSkills === []) {
            $flags[] = 'no_direct_skill_match';
        }

        if ($experiences === []) {
            $flags[] = 'no_structured_experience';
        }

        return $flags;
    }

    private function normalizeText(string $value): string
    {
        return mb_strtolower(trim($value));
    }
}
