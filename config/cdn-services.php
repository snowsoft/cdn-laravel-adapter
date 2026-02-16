<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CDN Services Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for CDN Services Node.js backend integration
    |
    */

    'base_url' => env('CDN_SERVICES_BASE_URL', 'http://localhost:3012'),
    
    'token' => env('CDN_SERVICES_TOKEN', null),
    'api_key' => env('CDN_SERVICES_API_KEY', null),
    'bearer_token' => env('CDN_SERVICES_BEARER_TOKEN', null),
    
    /*
    |--------------------------------------------------------------------------
    | Registration Token (Kullanıcı kaydı)
    |--------------------------------------------------------------------------
    | Backend'de REGISTRATION_TOKEN tanımlıysa kayıt için zorunludur.
    | CdnServicesAuthService::register() bu değeri kullanır (veya parametre ile geçilebilir).
    */
    'registration_token' => env('CDN_SERVICES_REGISTRATION_TOKEN', null),
    
    'disk' => env('CDN_SERVICES_DISK', 'local'),
    
    'timeout' => env('CDN_SERVICES_TIMEOUT', 30),
    
    'retry' => [
        'times' => env('CDN_SERVICES_RETRY_TIMES', 3),
        'sleep' => env('CDN_SERVICES_RETRY_SLEEP', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Storage Disk
    |--------------------------------------------------------------------------
    |
    | The default disk to use when uploading files to CDN Services
    | Options: local, s3, azure, gcs
    |
    */
    'default_disk' => env('CDN_SERVICES_DEFAULT_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Image Processing Options
    |--------------------------------------------------------------------------
    |
    | Default image processing options for uploaded images
    |
    */
    'image' => [
        'max_size' => env('CDN_SERVICES_MAX_SIZE', 52428800), // 50MB
        'allowed_types' => ['jpeg', 'jpg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tiff'],
        'default_format' => 'webp',
        'default_quality' => 85,
        'default_visibility' => env('CDN_SERVICES_DEFAULT_VISIBILITY', 'public'), // public|private|unlisted
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Cache settings for CDN Services responses
    |
    */
    'cache' => [
        'enabled' => env('CDN_SERVICES_CACHE_ENABLED', true),
        'ttl' => env('CDN_SERVICES_CACHE_TTL', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloudflare Image Resizing
    |--------------------------------------------------------------------------
    | Zone URL (resizing’in açık olduğu domain). Boş ise cloudflareProcessedUrl null döner.
    | Backend’de CLOUDFLARE_IMAGE_RESIZING_ENABLED + CLOUDFLARE_IMAGE_RESIZING_BASE_URL ile uyumlu.
    */
    'cloudflare_image_resizing_base_url' => env('CDN_SERVICES_CLOUDFLARE_IMAGE_RESIZING_BASE_URL', null),
];

