<?php

namespace App\Services\Resume;

use App\Models\Resume;

class ResumeSnapshotDiffService
{
    public function __construct(private readonly ResumeSnapshotBuilder $snapshotBuilder)
    {
    }

    public function compareWithCurrentProfile(Resume $resume): array
    {
        $current = $this->snapshotBuilder->build($resume->user, $resume->tailored_summary);
        $saved = $resume->profile_snapshot ?? [];

        $changes = [
            'identity' => $this->changedIdentity($saved['identity'] ?? [], $current['identity'] ?? []),
            'skills' => $this->countDifference($saved['skills'] ?? [], $current['skills'] ?? []),
            'experiences' => $this->countDifference($saved['experiences'] ?? [], $current['experiences'] ?? []),
            'educations' => $this->countDifference($saved['educations'] ?? [], $current['educations'] ?? []),
            'projects' => $this->countDifference($saved['projects'] ?? [], $current['projects'] ?? []),
            'certifications' => $this->countDifference($saved['certifications'] ?? [], $current['certifications'] ?? []),
            'languages' => $this->countDifference($saved['languages'] ?? [], $current['languages'] ?? []),
        ];

        $changed = collect($changes)->contains(fn (array $change) => $change['changed']);

        return [
            'has_changes' => $changed,
            'saved_hash' => $resume->snapshot_hash,
            'current_hash' => $this->snapshotBuilder->hash($current),
            'changes' => $changes,
        ];
    }

    private function changedIdentity(array $saved, array $current): array
    {
        $fields = ['name', 'email', 'phone', 'headline', 'current_title', 'current_company', 'summary'];
        $changedFields = collect($fields)
            ->filter(fn (string $field) => ($saved[$field] ?? null) !== ($current[$field] ?? null))
            ->values()
            ->all();

        return [
            'changed' => $changedFields !== [],
            'saved_count' => count(array_filter($saved)),
            'current_count' => count(array_filter($current)),
            'fields' => $changedFields,
        ];
    }

    private function countDifference(array $saved, array $current): array
    {
        return [
            'changed' => count($saved) !== count($current) || $this->hashItems($saved) !== $this->hashItems($current),
            'saved_count' => count($saved),
            'current_count' => count($current),
            'fields' => [],
        ];
    }

    private function hashItems(array $items): string
    {
        return hash('sha256', json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
