<?php

/**
 * CDN Services GraphQL şeması için type, query ve mutation tanımları.
 * config/graphql.php içinde bu dosyayı merge edin veya ilgili dizilere ekleyin.
 *
 * Örnek merge (config/graphql.php içinde):
 * 'types' => array_merge([
 *     ...
 * ], require __DIR__ . '/../vendor/cdn-services/laravel/config/graphql-cdn-services.php')['types']),
 * veya aynı projede ise: require base_path('packages/cdn-services/laravel/config/graphql-cdn-services.php')
 */
return [
    'types' => [
        'CdnImage' => \CdnServices\GraphQL\Types\CdnImageType::class,
        'Upload' => \CdnServices\GraphQL\Scalars\UploadScalar::class,
    ],
    'queries' => [
        'cdnImage' => \CdnServices\GraphQL\Queries\CdnImageQuery::class,
        'cdnImages' => \CdnServices\GraphQL\Queries\CdnImagesQuery::class,
    ],
    'mutations' => [
        'uploadCdnImage' => \CdnServices\GraphQL\Mutations\UploadImageMutation::class,
        'uploadCdnImageBase64' => \CdnServices\GraphQL\Mutations\UploadImageFromBase64Mutation::class,
        'deleteCdnImage' => \CdnServices\GraphQL\Mutations\DeleteImageMutation::class,
    ],
];
