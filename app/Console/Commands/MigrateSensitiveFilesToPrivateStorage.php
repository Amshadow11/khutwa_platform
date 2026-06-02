<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateSensitiveFilesToPrivateStorage extends Command
{
    protected $signature = 'files:migrate-sensitive-to-private {--delete-public : Delete public copies after a successful copy}';

    protected $description = 'Copy existing CVs and message attachments from public storage to the configured private disk.';

    public function handle(): int
    {
        $privateDisk = config('files.sensitive_disk', 'private');
        $deletePublic = (bool) $this->option('delete-public');
        $migrated = 0;

        Application::query()
            ->whereNotNull('cv_path')
            ->where('cv_path', 'not like', 'uploads/%')
            ->chunkById(100, function ($applications) use ($privateDisk, $deletePublic, &$migrated) {
                foreach ($applications as $application) {
                    $migrated += $this->copyToPrivate($application->cv_path, $privateDisk, $deletePublic) ? 1 : 0;
                }
            });

        Message::query()
            ->whereNotNull('attachment_path')
            ->chunkById(100, function ($messages) use ($privateDisk, $deletePublic, &$migrated) {
                foreach ($messages as $message) {
                    $migrated += $this->copyToPrivate($message->attachment_path, $privateDisk, $deletePublic) ? 1 : 0;
                }
            });

        $this->info("Migrated {$migrated} sensitive file(s) to {$privateDisk}.");

        return self::SUCCESS;
    }

    private function copyToPrivate(string $path, string $privateDisk, bool $deletePublic): bool
    {
        if (Storage::disk($privateDisk)->exists($path)) {
            return false;
        }

        if (! Storage::disk('public')->exists($path)) {
            $this->warn("Missing public file: {$path}");

            return false;
        }

        Storage::disk($privateDisk)->put($path, Storage::disk('public')->get($path));

        if ($deletePublic) {
            Storage::disk('public')->delete($path);
        }

        return true;
    }
}
