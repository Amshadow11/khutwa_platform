<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Services\Resume\ResumeSnapshotBuilder;
use Illuminate\Console\Command;

class BackfillApplicationResumeSnapshots extends Command
{
    protected $signature = 'applications:backfill-resume-snapshots {--chunk=100}';

    protected $description = 'Backfill immutable resume snapshots for legacy applications.';

    public function handle(ResumeSnapshotBuilder $snapshotBuilder): int
    {
        $updated = 0;

        Application::query()
            ->with('user')
            ->whereNull('resume_snapshot')
            ->whereHas('user')
            ->orderBy('id')
            ->chunkById((int) $this->option('chunk'), function ($applications) use ($snapshotBuilder, &$updated) {
                foreach ($applications as $application) {
                    $snapshot = $snapshotBuilder->build($application->user);
                    $snapshot['snapshot_source'] = 'legacy_backfill';
                    $snapshot['legacy_application_id'] = $application->id;

                    $application->forceFill([
                        'resume_snapshot' => $snapshot,
                        'resume_snapshot_hash' => $snapshotBuilder->hash($snapshot),
                        'resume_snapshot_version' => $snapshot['snapshot_schema'] ?? 1,
                        'resume_snapshot_created_at' => $application->applied_at ?: now(),
                        'submitted_resume_pdf_path' => $application->cv_path,
                    ])->save();

                    $updated++;
                }
            });

        $this->info("Backfilled {$updated} application resume snapshots.");

        return self::SUCCESS;
    }
}
