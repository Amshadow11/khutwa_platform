<?php

namespace App\Services\Resume;

use App\Models\Resume;
use App\Models\User;
use App\Services\Profile\ProfileCompletenessService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResumeCompositionService
{
    public function __construct(
        private readonly ProfileCompletenessService $completeness,
        private readonly ResumeTemplateResolver $templateResolver,
        private readonly ResumeSnapshotBuilder $snapshotBuilder,
    ) {}

    public function createFromProfile(User $user, array $attributes = []): Resume
    {
        return DB::transaction(function () use ($user, $attributes) {
            $template = $this->templateResolver->resolve($attributes['template_slug'] ?? null);
            $score = $this->completeness->calculate($user)['score'];
            $snapshot = $this->snapshotBuilder->build($user, $attributes['tailored_summary'] ?? null);

            $resume = Resume::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'template_id' => $template?->id,
                'target_job_id' => $attributes['target_job_id'] ?? null,
                'title' => $attributes['title'] ?? 'Professional Resume',
                'slug' => $this->uniqueSlug($user, $attributes['title'] ?? 'professional-resume'),
                'visibility' => $attributes['visibility'] ?? 'private',
                'public_token' => Str::random(48),
                'private_share_token' => Str::random(48),
                'is_default' => $attributes['is_default'] ?? ! $user->resumes()->exists(),
                'locale' => $attributes['locale'] ?? 'ar',
                'direction' => $attributes['direction'] ?? 'rtl',
                'tailored_summary' => $attributes['tailored_summary'] ?? null,
                'settings' => $attributes['settings'] ?? [],
                'profile_snapshot' => $snapshot,
                'snapshot_version' => 1,
                'snapshot_hash' => $this->snapshotBuilder->hash($snapshot),
                'snapshot_created_at' => now(),
                'completeness_score' => $score,
                'seo_metadata' => $this->seoMetadata($user, $attributes['title'] ?? 'Professional Resume'),
            ]);

            foreach ($this->defaultSections() as $index => $section) {
                $resume->sections()->create([
                    'section_key' => $section['key'],
                    'title' => $section['title'],
                    'source_type' => 'profile',
                    'is_visible' => true,
                    'sort_order' => $index + 1,
                    'settings' => $section['settings'] ?? [],
                ]);
            }

            return $resume->load('sections', 'template');
        });
    }

    private function uniqueSlug(User $user, string $title): string
    {
        $base = Str::slug($title) ?: 'resume';
        $slug = $base;
        $counter = 2;

        while ($user->resumes()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function defaultSections(): array
    {
        return [
            ['key' => 'summary', 'title' => 'Summary'],
            ['key' => 'skills', 'title' => 'Skills'],
            ['key' => 'experience', 'title' => 'Experience'],
            ['key' => 'education', 'title' => 'Education'],
            ['key' => 'projects', 'title' => 'Projects'],
            ['key' => 'certifications', 'title' => 'Certifications'],
            ['key' => 'languages', 'title' => 'Languages'],
            ['key' => 'links', 'title' => 'Links'],
        ];
    }

    private function seoMetadata(User $user, string $title): array
    {
        return [
            'title' => "{$title} - {$user->display_name}",
            'description' => str($user->professionalProfile?->headline ?: $user->bio ?: $user->display_name)->limit(155)->toString(),
            'robots' => 'index,follow',
        ];
    }
}
