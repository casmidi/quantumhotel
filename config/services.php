<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'paddle_ocr' => [
        'python_path' => env('PADDLE_OCR_PYTHON_PATH'),
        'script_path' => env('PADDLE_OCR_SCRIPT_PATH'),
        'lang' => env('PADDLE_OCR_LANG', 'en'),
    ],

    'google_document_ai' => [
        'project_id' => env('GOOGLE_DOCUMENT_AI_PROJECT_ID'),
        'location' => env('GOOGLE_DOCUMENT_AI_LOCATION', 'us'),
        'processor_id' => env('GOOGLE_DOCUMENT_AI_PROCESSOR_ID'),
        'processor_version' => env('GOOGLE_DOCUMENT_AI_PROCESSOR_VERSION'),
        'endpoint' => env('GOOGLE_DOCUMENT_AI_ENDPOINT', 'https://documentai.googleapis.com'),
        'credentials_path' => env('GOOGLE_DOCUMENT_AI_CREDENTIALS_PATH'),
        'credentials_json' => env('GOOGLE_DOCUMENT_AI_CREDENTIALS_JSON'),
        'language_hints' => array_values(array_filter(array_map(
            static fn ($value) => trim((string) $value),
            explode(',', (string) env('GOOGLE_DOCUMENT_AI_LANGUAGE_HINTS', 'id,en'))
        ))),
    ],

    'openai_ocr' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_OCR_MODEL', 'gpt-5.4'),
        'detail' => env('OPENAI_OCR_DETAIL', 'high'),
    ],

];
