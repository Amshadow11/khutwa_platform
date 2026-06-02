<?php

namespace App\Actions\Resume;

use App\Models\Resume;
use App\Services\Profile\ProfileCompletenessService;
use App\Services\Resume\ResumeSnapshotBuilder;
use Illuminate\Support\Facades\DB;

class UpdateResumeAction
{
    public function __construct(
        private readonly ResumeSnapshotBuilder $snapshotBuilder,
        private readonly ProfileCompletenessService $completeness,
    ) {
    }

    public function execute(Resume $resume, array $data): Resume
    {
        return DB::transaction(function () use ($resume, $data) {
            $locale = $data['locale'] ?? $resume->locale;
            $summaryChanged = array_key_exists('tailored_summary', $data)
                && $resume->tailored_summary !== $data['tailored_summary'];
            $renderChanged = $summaryChanged
                || (int) ($data['template_id'] ?? $resume->template_id) !== (int) $resume->template_id
                || ($data['direction'] ?? $resume->direction) !== $resume->direction
                || ($data['locale'] ?? $resume->locale) !== $resume->locale
                || array_key_exists('sections', $data);

            $resume->fill(collect($data)->only([
                'title',
                'template_id',
                'visibility',
                'locale',
                'direction',
                'tailored_summary',
                'settings',
                'seo_metadata',
            ])->all());

            if (($data['visibility'] ?? null) === 'public' && ! $resume->published_at) {
                $resume->published_at = now();
            }

            if (($data['visibility'] ?? null) === 'private') {
                $resume->published_at = null;
            }

            $resume->save();

            if ($summaryChanged) {
                $snapshot = $this->snapshotBuilder->build($resume->user, $resume->tailored_summary);
                $resume->forceFill([
                    'profile_snapshot' => $snapshot,
                    'snapshot_version' => $resume->snapshot_version + 1,
                    'snapshot_hash' => $this->snapshotBuilder->hash($snapshot),
                    'snapshot_created_at' => now(),
                    'completeness_score' => $this->completeness->calculate($resume->user)['score'],
                    'generated_pdf_path' => null,
                    'last_generated_at' => null,
                    'pdf_status' => 'not_generated',
                    'pdf_error' => null,
                ])->save();
            } elseif ($renderChanged) {
                $resume->forceFill([
                    'generated_pdf_path' => null,
                    'last_generated_at' => null,
                    'pdf_status' => 'not_generated',
                    'pdf_error' => null,
                ])->save();
            }

            foreach ($data['sections'] ?? [] as $sectionId => $sectionData) {
                $section = $resume->sections()->whereKey($sectionId)->first();

                if (! $section) {
                    continue;
                }

                $section->update([
                    'title' => $this->sectionTitle($section->section_key, $sectionData['title'] ?? $section->title, $locale),
                    'is_visible' => (bool) ($sectionData['is_visible'] ?? false),
                    'sort_order' => (int) ($sectionData['sort_order'] ?? $section->sort_order),
                    'settings' => [
                        'featured_only' => (bool) ($sectionData['featured_only'] ?? false),
                        'limit' => (int) ($sectionData['limit'] ?? 0),
                    ],
                ]);
            }

            return $resume->fresh(['template', 'sections']);
        });
    }

    private function sectionTitle(string $key, ?string $title, ?string $locale): string
    {
        $labels = config("resumes.sections.{$key}", []);

        if (! is_array($labels)) {
            return $title ?: (string) $labels;
        }

        $defaultLabels = array_filter($labels);

        if ($title && ! in_array($title, $defaultLabels, true)) {
            return $title;
        }

        $locale = str($locale ?: 'en')->before('-')->lower()->toString();

        return $labels[$locale] ?? $labels['en'] ?? $key;
    }
}
