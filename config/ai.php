<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Provider لكل Feature
    |--------------------------------------------------------------------------
    |
    | gemini → مجاني + سريع (للميزات العامة)
    | openai → أدق (للميزات التي تحتاج تحليل منطقي عميق)
    |
    */

    'features' => [
        'cv_parser'           => env('AI_CV_PARSER_PROVIDER', 'gemini'),
        'cover_letter'        => env('AI_COVER_LETTER_PROVIDER', 'gemini'),
        'job_description'     => env('AI_JOB_DESCRIPTION_PROVIDER', 'gemini'),
        'job_matching'        => env('AI_JOB_MATCHING_PROVIDER', 'openai'),
        'screening_questions' => env('AI_SCREENING_PROVIDER', 'gemini'),
        'auto_filtering'      => env('AI_AUTO_FILTERING_PROVIDER', 'openai'),
        'smart_search'        => env('AI_SMART_SEARCH_PROVIDER', 'gemini'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    */

    'models' => [
        'openai' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'gemini' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    ],

    /*
    |--------------------------------------------------------------------------
    | تكلفة كل 1000 token بالدولار
    | تُستخدم لحساب التكلفة في ai_usage_logs
    |--------------------------------------------------------------------------
    */

    'costs' => [
        'gpt-4o-mini' => [
            'input'  => 0.000150,  // $0.15 / 1M tokens
            'output' => 0.000600,  // $0.60 / 1M tokens
        ],
        'gemini-1.5-flash' => [
            'input'  => 0.000000,  // مجاني في Free Tier
            'output' => 0.000000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTL بالدقائق
    |--------------------------------------------------------------------------
    */

    'cache_ttl' => [
        'job_matching'    => 1440, // 24 ساعة
        'smart_search'    => 60,   // ساعة واحدة
        'cv_parser'       => 10080, // أسبوع
    ],

    'matching' => [
        'version' => (int) env('AI_MATCHING_VERSION', 1),
        'enable_explanations' => (bool) env('AI_MATCHING_EXPLANATIONS', true),
    ],
];
