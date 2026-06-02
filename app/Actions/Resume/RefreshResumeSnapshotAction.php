<?php

namespace App\Actions\Resume;

use App\Models\Resume;
use App\Services\Profile\ProfileCompletenessService;
use App\Services\Resume\ResumeSnapshotBuilder;

class RefreshResumeSnapshotAction
{
    public function __construct(
        private readonly ResumeSnapshotBuilder $snapshotBuilder,
        private readonly ProfileCompletenessService $completeness,
    ) {
    }

    public function execute(Resume $resume): Resume
    {
        $snapshot = $this->snapshotBuilder->build($resume->user, $resume->tailored_summary);

        $resume->forceFill([
            'profile_snapshot' => $snapshot,
            'snapshot_version' => $resume->snapshot_version + 1,
            'snapshot_hash' => $this->snapshotBuilder->hash($snapshot),
            'snapshot_created_at' => now(),
            'completeness_score' => $this->completeness->calculate($resume->user)['score'],
            'generated_pdf_path' => null,
            'last_generated_at' => null,
        ])->save();

        return $resume->fresh(['template', 'sections']);
    }
}
