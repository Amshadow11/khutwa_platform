<?php

namespace App\Data\Search;

use Illuminate\Http\Request;

readonly class JobSearchFilters
{
    public function __construct(
        public ?string $keyword = null,
        public ?string $location = null,
        public ?string $jobType = null,
        public ?string $category = null,
        public ?string $experienceLevel = null,
        public bool $remoteWork = false,
        public bool $urgent = false,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            keyword: self::stringOrNull($request->input('keyword')),
            location: self::stringOrNull($request->input('location')),
            jobType: self::stringOrNull($request->input('job_type')),
            category: self::stringOrNull($request->input('category')),
            experienceLevel: self::stringOrNull($request->input('experience_level')),
            remoteWork: $request->has('remote_work'),
            urgent: $request->has('urgent'),
        );
    }

    public function toViewArray(): array
    {
        return array_filter([
            'keyword' => $this->keyword,
            'location' => $this->location,
            'job_type' => $this->jobType,
            'category' => $this->category,
            'experience_level' => $this->experienceLevel,
            'remote_work' => $this->remoteWork,
            'urgent' => $this->urgent,
        ], fn (mixed $value): bool => $value !== null && $value !== false && $value !== '');
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
