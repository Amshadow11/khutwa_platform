<?php

namespace App\Services\Profile;

use App\Models\User;

class ProfileCompletenessService
{
    public function calculate(User $user): array
    {
        $user->loadMissing([
            'professionalProfile',
            'canonicalSkills',
            'experiences',
            'educations',
            'projects',
            'certifications',
            'languages',
        ]);

        $checks = [
            'basic_identity' => [
                'weight' => 15,
                'complete' => filled($user->full_name) && filled($user->email),
                'suggestion' => 'أكمل الاسم والمعلومات الأساسية.',
            ],
            'headline_summary' => [
                'weight' => 15,
                'complete' => filled($user->professionalProfile?->headline) || filled($user->bio),
                'suggestion' => 'أضف عنوانًا مهنيًا ونبذة مختصرة واضحة.',
            ],
            'skills' => [
                'weight' => 20,
                'complete' => $user->canonicalSkills->count() >= 3,
                'suggestion' => 'أضف 3 مهارات موحدة على الأقل مع مستوى الخبرة.',
            ],
            'experience' => [
                'weight' => 20,
                'complete' => $user->experiences->isNotEmpty(),
                'suggestion' => 'أضف خبرة عملية واحدة على الأقل.',
            ],
            'education' => [
                'weight' => 10,
                'complete' => $user->educations->isNotEmpty(),
                'suggestion' => 'أضف مؤهلك التعليمي أو تدريبك المهني.',
            ],
            'projects_or_certifications' => [
                'weight' => 10,
                'complete' => $user->projects->isNotEmpty() || $user->certifications->isNotEmpty(),
                'suggestion' => 'أضف مشروعًا أو شهادة لتعزيز قابلية المطابقة.',
            ],
            'languages' => [
                'weight' => 5,
                'complete' => $user->languages->isNotEmpty(),
                'suggestion' => 'أضف اللغات ومستوى الإتقان.',
            ],
            'professional_links' => [
                'weight' => 5,
                'complete' => filled($user->linkedin_url) || filled($user->github_url) || filled($user->portfolio_url),
                'suggestion' => 'أضف رابطًا مهنيًا مثل LinkedIn أو Portfolio.',
            ],
        ];

        $score = collect($checks)
            ->filter(fn (array $check) => $check['complete'])
            ->sum('weight');

        return [
            'score' => min(100, $score),
            'strength' => $this->strengthForScore($score),
            'checks' => $checks,
            'missing' => collect($checks)
                ->reject(fn (array $check) => $check['complete'])
                ->keys()
                ->values()
                ->all(),
            'suggestions' => collect($checks)
                ->reject(fn (array $check) => $check['complete'])
                ->map(fn (array $check, string $key) => [
                    'key' => $key,
                    'weight' => $check['weight'],
                    'message' => $check['suggestion'],
                ])
                ->values()
                ->all(),
        ];
    }

    public function refresh(User $user): array
    {
        $result = $this->calculate($user);

        $profile = $user->professionalProfile()->firstOrCreate([]);
        $profile->update([
            'profile_completeness_score' => $result['score'],
            'profile_completed_at' => $result['score'] >= 80 ? now() : null,
        ]);

        return $result;
    }

    private function strengthForScore(int|float $score): string
    {
        return match (true) {
            $score >= 85 => 'excellent',
            $score >= 70 => 'strong',
            $score >= 45 => 'building',
            default => 'starter',
        };
    }
}
