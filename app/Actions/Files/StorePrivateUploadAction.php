<?php

namespace App\Actions\Files;

use App\Services\Files\PrivateFileStorageService;
use App\Support\Files\PrivateFileResult;
use Illuminate\Http\UploadedFile;

class StorePrivateUploadAction
{
    public function __construct(private readonly PrivateFileStorageService $storage) {}

    public function execute(UploadedFile $file, string $directory, array $allowedMimes): PrivateFileResult
    {
        $realMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->path());

        abort_unless(in_array($realMime, $allowedMimes, true), 422, 'نوع الملف غير مدعوم.');

        return $this->storage->storeUploadedFile($file, $directory);
    }
}
