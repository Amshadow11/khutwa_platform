<?php

use App\Models\Job;
use App\Models\JobApplicationMatch;

return [
    'driver' => env('SCOUT_DRIVER', 'meilisearch'),
    'prefix' => env('SCOUT_PREFIX', ''),
    'queue' => env('SCOUT_QUEUE', true),
    'after_commit' => true,
    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],
    'soft_delete' => false,
    'identify' => env('SCOUT_IDENTIFY', false),
    'algolia' => [
        'id' => env('ALGOLIA_APP_ID', ''),
        'secret' => env('ALGOLIA_SECRET', ''),
        'index-settings' => [],
    ],
    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            'jobs' => [
                'filterableAttributes' => [
                    'id',
                    'company_id',
                    'company_verified',
                    'status',
                    'is_active',
                    'category',
                    'job_type',
                    'experience_level',
                    'location',
                    'remote_work',
                    'urgent',
                    'featured',
                ],
                'sortableAttributes' => [
                    'created_at',
                    'post_date',
                    'deadline',
                ],
                'searchableAttributes' => [
                    'title',
                    'description',
                    'requirements',
                    'benefits',
                    'company_name',
                    'location',
                    'category',
                    'job_type',
                    'experience_level',
                ],
            ],
            'job_application_matches' => [
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
            ],
        ],
    ],
    'typesense' => [
        'client-settings' => [
            'api_key' => env('TYPESENSE_API_KEY', 'xyz'),
            'nodes' => [
                [
                    'host' => env('TYPESENSE_HOST', 'localhost'),
                    'port' => env('TYPESENSE_PORT', '8108'),
                    'path' => env('TYPESENSE_PATH', ''),
                    'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
                ],
            ],
            'connection_timeout_seconds' => env('TYPESENSE_CONNECTION_TIMEOUT_SECONDS', 2),
        ],
        'model-settings' => [
            Job::class => [],
            JobApplicationMatch::class => [],
        ],
        'import_action' => env('TYPESENSE_IMPORT_ACTION', 'upsert'),
    ],
];
