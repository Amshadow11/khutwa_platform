<?php

namespace App\Services\Matching;

class JobMatchingPromptBuilder
{
    public function system(): string
    {
        return 'You are an ATS assistant. Explain a job/candidate match using only the provided job and resume snapshot data. Be concise, fair, and avoid protected-class assumptions.';
    }

    public function prompt(array $input, array $score): string
    {
        return json_encode([
            'task' => 'Write a concise recruiter-facing match explanation.',
            'job' => $input['job'],
            'candidate_snapshot' => $input['candidate'],
            'score' => $score,
            'format' => '2-4 short sentences. Mention strongest matches and important gaps.',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
