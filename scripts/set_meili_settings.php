<?php
$url = 'http://127.0.0.1:7700/indexes/job_application_matches/settings';
$data = [
    'filterableAttributes' => [
        'company_id',
        'job_id',
        'application_id',
        'job_match_run_id',
        'overall_score',
        'skills_score',
        'experience_score',
        'status',
        'matching_version',
        'is_reused',
        'evaluated_at',
    ],
    'sortableAttributes' => [
        'overall_score',
        'skills_score',
        'experience_score',
        'evaluated_at',
    ],
    'searchableAttributes' => [
        'candidate_name',
        'candidate_headline',
        'candidate_location',
        'matched_skills',
        'missing_skills',
        'risk_flags',
    ],
];
$options = [
    'http' => [
        'method' => 'PUT',
        'header' => "Content-Type: application/json\r\n",
        'content' => json_encode($data),
        'timeout' => 10,
    ],
];
$context = stream_context_create($options);
$result = @file_get_contents($url, false, $context);
if ($result === false) {
    $error = error_get_last();
    echo "ERROR: ".json_encode($error).PHP_EOL;
} else {
    echo $result.PHP_EOL;
}
