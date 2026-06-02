<?php

namespace App\Actions\Applications;

use App\Models\Resume;
use App\Models\User;
use App\Services\Resume\ResumeSnapshotBuilder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CaptureApplicationResumeSnapshotAction
{
    public function __construct(private readonly ResumeSnapshotBuilder $snapshotBuilder) {}

    public function execute(User $user, ?Resume $resume = null, ?string $uploadedCvPath = null): array
    {
        if ($resume) {
            $snapshot = $resume->profile_snapshot ?: $this->snapshotBuilder->build($user, $resume->tailored_summary);
            $createdAt = $resume->snapshot_created_at ?: now();

            return [
                'resume_id' => $resume->id,
                'resume_snapshot' => $snapshot,
                'resume_snapshot_hash' => $resume->snapshot_hash ?: $this->snapshotBuilder->hash($snapshot),
                'resume_snapshot_version' => $resume->snapshot_version ?: $resume->version_number ?: 1,
                'resume_snapshot_created_at' => $createdAt,
                'submitted_resume_pdf_path' => $this->copyResumePdfForApplication($resume),
            ];
        }

        $snapshot = $this->snapshotBuilder->build($user);

        return [
            'resume_id' => null,
            'resume_snapshot' => $snapshot,
            'resume_snapshot_hash' => $this->snapshotBuilder->hash($snapshot),
            'resume_snapshot_version' => $snapshot['snapshot_schema'] ?? 1,
            'resume_snapshot_created_at' => now(),
            'submitted_resume_pdf_path' => $uploadedCvPath,
        ];
    }

    private function copyResumePdfForApplication(Resume $resume): ?string
    {
        if (! $resume->generated_pdf_path) {
            return null;
        }

        $sourceDisk = config('resumes.disk', 'private');
        $targetDisk = config('files.sensitive_disk', 'private');

        if (! Storage::disk($sourceDisk)->exists($resume->generated_pdf_path)) {
            return null;
        }

        $targetPath = sprintf(
            'application-resumes/%s/%s-%s.pdf',
            now()->format('Y/m'),
            $resume->user_id,
            Str::uuid()
        );

        Storage::disk($targetDisk)->put(
            $targetPath,
            Storage::disk($sourceDisk)->get($resume->generated_pdf_path)
        );

        return $targetPath;
    }
}
