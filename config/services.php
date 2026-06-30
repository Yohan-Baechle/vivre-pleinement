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

    'youtube' => [
        'api_key' => env('YOUTUBE_API_KEY'),
        'channel_id' => env('YOUTUBE_CHANNEL_ID'),
        // OAuth propriétaire de la chaîne, requis pour captions.download.
        'oauth_client_id' => env('YOUTUBE_OAUTH_CLIENT_ID'),
        'oauth_client_secret' => env('YOUTUBE_OAUTH_CLIENT_SECRET'),
        'oauth_refresh_token' => env('YOUTUBE_OAUTH_REFRESH_TOKEN'),
    ],

    'brevo' => [
        'key' => env('BREVO_API_KEY'),
        'video_list_id' => (int) env('BREVO_VIDEO_LIST_ID', 6),
        'doi_template_id' => (int) env('BREVO_DOI_TEMPLATE_ID', 6),
    ],
];
