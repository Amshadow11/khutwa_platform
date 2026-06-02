<?php

namespace App\Services\Resume;

use App\Models\Resume;
use Illuminate\Support\Collection;

class ResumeSectionDataBuilder
{
    public function build(Resume $resume): Collection
    {
        $snapshot = $resume->profile_snapshot ?? [];

        return $resume->sections
            ->where('is_visible', true)
            ->map(fn ($section) => [
                'key' => $section->section_key,
                'title' => $this->sectionTitle($section->section_key, $section->title, $resume->locale),
                'settings' => $section->settings ?? [],
                'items' => $this->itemsFor($section->section_key, $snapshot, $section->settings ?? []),
            ])
            ->filter(fn (array $section) => filled($section['items']) || $section['key'] === 'summary')
            ->values();
    }

    private function sectionTitle(string $key, ?string $storedTitle, ?string $locale): string
    {
        $labels = config("resumes.sections.{$key}", []);
        $locale = str($locale ?: 'en')->before('-')->lower()->toString();

        if (is_string($labels)) {
            return $storedTitle ?: $labels;
        }

        $defaultLabels = array_filter($labels);

        if ($storedTitle && ! in_array($storedTitle, $defaultLabels, true)) {
            return $storedTitle;
        }

        return $labels[$locale] ?? $labels['en'] ?? $key;
    }

    private function itemsFor(string $key, array $snapshot, array $settings): mixed
    {
        return match ($key) {
            'summary' => $snapshot['identity']['summary'] ?? null,
            'skills' => $this->skills($snapshot['skills'] ?? [], $settings),
            'experience' => $this->limit($snapshot['experiences'] ?? [], $settings),
            'education' => $this->limit($snapshot['educations'] ?? [], $settings),
            'projects' => $this->projects($snapshot['projects'] ?? [], $settings),
            'certifications' => $this->limit($snapshot['certifications'] ?? [], $settings),
            'languages' => $this->limit($snapshot['languages'] ?? [], $settings),
            'links' => array_filter($snapshot['identity']['links'] ?? []),
            default => [],
        };
    }

    private function skills(array $skills, array $settings): array
    {
        if (($settings['featured_only'] ?? false) === true) {
            return array_values(array_filter($skills, fn (array $skill) => $skill['featured'] ?? false));
        }

        return $this->limit($skills, $settings);
    }

    private function projects(array $projects, array $settings): array
    {
        if (($settings['featured_only'] ?? false) === true) {
            return array_values(array_filter($projects, fn (array $project) => $project['featured'] ?? false));
        }

        return $this->limit($projects, $settings);
    }

    private function limit(array $items, array $settings): array
    {
        $limit = (int) ($settings['limit'] ?? 0);

        if ($limit <= 0) {
            return $items;
        }

        return array_slice($items, 0, $limit);
    }
}
