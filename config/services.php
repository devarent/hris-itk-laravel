<?php
return [
    'mailgun' => ['domain' => env('MAILGUN_DOMAIN'), 'secret' => env('MAILGUN_SECRET'), 'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net')],
    'postmark' => ['token' => env('POSTMARK_TOKEN')],
    'ses' => ['key' => env('AWS_ACCESS_KEY_ID'), 'secret' => env('AWS_SECRET_ACCESS_KEY'), 'region' => env('AWS_DEFAULT_REGION', 'us-east-1')],
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'models' => [
            'hydrogen' => env('GEMINI_MODEL_HYDROGEN', 'gemini-4-hydrogen'),
            'neon' => env('GEMINI_MODEL_NEON', 'gemini-4-neon'),
            'carbon' => env('GEMINI_MODEL_CARBON', 'gemini-4-carbon'),
        ],
    ],
    'zai' => [
        'api_key' => env('ZAI_API_KEY'),
        'base_url' => env('ZAI_BASE_URL', 'https://api.z.ai/api/paas/v4'),
        'model' => env('ZAI_MODEL', 'glm-5.3'),
        'timeout' => (int) env('ZAI_TIMEOUT', 150),
    ],
];
