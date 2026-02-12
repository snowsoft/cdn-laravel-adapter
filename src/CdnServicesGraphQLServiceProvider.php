<?php

namespace CdnServices;

use Illuminate\Support\ServiceProvider;

/**
 * CDN Services GraphQL entegrasyonu (opsiyonel).
 * rebing/graphql-laravel yüklüyse types/queries/mutations config'e eklenir.
 */
class CdnServicesGraphQLServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/graphql-cdn-services.php',
            'graphql-cdn-services'
        );
    }

    public function boot()
    {
        if (!class_exists(\Rebing\GraphQL\GraphQL::class)) {
            return;
        }

        $cdnConfig = config('graphql-cdn-services', []);
        $graphqlConfig = config('graphql', []);

        $graphqlConfig['types'] = array_merge(
            $graphqlConfig['types'] ?? [],
            $cdnConfig['types'] ?? []
        );
        $graphqlConfig['queries'] = array_merge(
            $graphqlConfig['queries'] ?? [],
            $cdnConfig['queries'] ?? []
        );
        $graphqlConfig['mutations'] = array_merge(
            $graphqlConfig['mutations'] ?? [],
            $cdnConfig['mutations'] ?? []
        );

        config(['graphql.types' => $graphqlConfig['types']]);
        config(['graphql.queries' => $graphqlConfig['queries']]);
        config(['graphql.mutations' => $graphqlConfig['mutations']]);
    }
}
