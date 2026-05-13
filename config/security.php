<?php

$emails = array_filter(array_map(
    static fn (string $email): string => strtolower(trim($email)),
    explode(',', (string) env('ADMIN_ALLOWED_EMAILS', ''))
));

$trustedProxies = array_values(array_filter(array_map(
    static fn (string $proxy): string => trim($proxy),
    explode(',', (string) env('TRUSTED_PROXIES', ''))
)));

return [
    'admin_allowed_emails' => array_values($emails),
    'trusted_proxies' => $trustedProxies,
    'forms' => [
        'honeypot_field' => env('FORM_HONEYPOT_FIELD', 'website'),
        'max_attempts' => (int) env('FORM_RATE_LIMIT_MAX_ATTEMPTS', 5),
        'decay_seconds' => (int) env('FORM_RATE_LIMIT_DECAY_SECONDS', 60),
    ],
    'uploads' => [
        'page_media' => [
            'image_max_kb' => (int) env('PAGE_MEDIA_IMAGE_MAX_KB', 20480),
            'video_max_kb' => (int) env('PAGE_MEDIA_VIDEO_MAX_KB', 102400),
            'image_mimes' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
            'image_mimetypes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
            'document_mimes' => ['pdf'],
            'document_mimetypes' => ['application/pdf'],
            'video_extensions' => ['mp4', 'webm', 'ogg', 'mov'],
            'video_mimetypes' => ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'],
        ],
    ],
];
