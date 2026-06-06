<?php

namespace App\Data\Matching;

use Illuminate\Http\Request;

class JobMatchSearchFilters
{
    public function __construct(
        public readonly ?string $keyword = null,
        public readonly ?int $jobId = null,
        public readonly ?int $runId = null,
        public readonly ?float $minScore = null,
        public readonly ?float $maxScore = null,
        public readonly ?string $status = null,
        public readonly ?bool $reused = null,
        public readonly string $sort = 'overall_score',
        public readonly string $direction = 'desc',
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            keyword: $request->string('q')->trim()->value() ?: null,
            jobId: $request->integer('job_id') ?: null,
            runId: $request->integer('run_id') ?: null,
            minScore: $request->filled('min_score') ? (float) $request->input('min_score') : null,
            maxScore: $request->filled('max_score') ? (float) $request->input('max_score') : null,
            status: $request->string('status')->trim()->value() ?: null,
            reused: $request->filled('reused') ? $request->boolean('reused') : null,
            sort: in_array($request->input('sort'), ['overall_score', 'skills_score', 'experience_score', 'evaluated_at'], true)
                ? $request->input('sort')
                : 'overall_score',
            direction: $request->input('direction') === 'asc' ? 'asc' : 'desc',
        );
    }
}
