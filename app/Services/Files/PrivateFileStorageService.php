<?php

namespace App\Services\Files;

use App\Support\Files\PrivateFileResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivateFileStorageService
{
    public function disk(): string
    {
        return config('files.sensitive_disk', 'private');
    }

    public function storeUploadedFile(UploadedFile $file, string $directory): PrivateFileResult
    {
        $path = $file->store($directory, $this->disk());

        return new PrivateFileResult(
            disk: $this->disk(),
            path: $path,
            originalName: $file->getClientOriginalName(),
            mimeType: $file->getMimeType() ?: 'application/octet-stream',
            size: $file->getSize() ?: 0,
        );
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->disk())->exists($path);
    }

    public function delete(?string $path): void
    {
        if ($path && $this->exists($path)) {
            Storage::disk($this->disk())->delete($path);
        }
    }

    public function download(string $path, ?string $name = null, array $headers = []): StreamedResponse
    {
        abort_unless($this->exists($path), 404);

        return Storage::disk($this->disk())->download($path, $name, $headers);
    }
}
