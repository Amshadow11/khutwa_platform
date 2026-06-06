<?php
require __DIR__ . '/../vendor/autoload.php';
$client = new \Meilisearch\Client('http://127.0.0.1:7700', '');
$index = $client->index('job_application_matches');
$settings = [
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
try {
    $res = $index->updateSettings($settings);
    echo "OK: ";
    var_export($res);
    echo PHP_EOL;
} catch (Exception $e) {
    echo "ERR: ".$e->getMessage().PHP_EOL;
}
