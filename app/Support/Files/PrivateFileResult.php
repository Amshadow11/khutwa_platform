<?php

namespace App\Support\Files;

final readonly class PrivateFileResult
{
    public function __construct(
        public string $disk,
        public string $path,
        public string $originalName,
        public string $mimeType,
        public int $size,
    ) {}
}
