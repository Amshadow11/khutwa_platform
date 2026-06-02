<?php

return [
    'default_template' => env('RESUME_DEFAULT_TEMPLATE', 'modern'),
    'disk' => env('RESUME_DISK', 'private'),
    'pdf_queue' => env('RESUME_PDF_QUEUE', 'pdf'),
    'pdf' => [
        'format' => env('RESUME_PDF_FORMAT', 'A4'),
        'margin_top' => (int) env('RESUME_PDF_MARGIN_TOP', 8),
        'margin_right' => (int) env('RESUME_PDF_MARGIN_RIGHT', 8),
        'margin_bottom' => (int) env('RESUME_PDF_MARGIN_BOTTOM', 8),
        'margin_left' => (int) env('RESUME_PDF_MARGIN_LEFT', 8),
        'node_binary' => env('BROWSERSHOT_NODE_BINARY'),
        'npm_binary' => env('BROWSERSHOT_NPM_BINARY'),
        'chrome_path' => env('BROWSERSHOT_CHROME_PATH'),
        'timeout' => (int) env('RESUME_PDF_TIMEOUT', 120),
    ],
    'sections' => [
        'summary' => [
            'en' => 'Summary',
            'ar' => 'الملخص',
        ],
        'skills' => [
            'en' => 'Skills',
            'ar' => 'المهارات',
        ],
        'experience' => [
            'en' => 'Experience',
            'ar' => 'الخبرات',
        ],
        'education' => [
            'en' => 'Education',
            'ar' => 'التعليم',
        ],
        'projects' => [
            'en' => 'Projects',
            'ar' => 'المشاريع',
        ],
        'certifications' => [
            'en' => 'Certifications',
            'ar' => 'الشهادات',
        ],
        'languages' => [
            'en' => 'Languages',
            'ar' => 'اللغات',
        ],
        'links' => [
            'en' => 'Links',
            'ar' => 'الروابط',
        ],
    ],
];
