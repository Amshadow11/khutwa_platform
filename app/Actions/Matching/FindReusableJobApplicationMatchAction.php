<?php

namespace App\Actions\Matching;

use App\Models\JobApplicationMatch;

class FindReusableJobApplicationMatchAction
{
    public function execute(string $cacheKey): ?JobApplicationMatch
    {
        return JobApplicationMatch::query()
            ->where('match_cache_key', $cacheKey)
            ->where('status', JobApplicationMatch::STATUS_COMPLETED)
            ->where('is_reused', false)
            ->latest('evaluated_at')
            ->first();
    }
}
