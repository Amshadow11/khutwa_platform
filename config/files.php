<?php

return [
    'sensitive_disk' => env('SENSITIVE_FILESYSTEM_DISK', 'private'),
    'signed_url_ttl_minutes' => (int) env('SIGNED_FILE_URL_TTL', 15),
    'allowed_cv_mimes' => ['application/pdf'],
    'allowed_message_attachment_mimes' => [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
    ],
];
