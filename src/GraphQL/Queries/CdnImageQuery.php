<?php

namespace CdnServices\GraphQL\Queries;

use Illuminate\Support\Facades\Storage;
use Rebing\GraphQL\Support\Query;
use GraphQL\Type\Definition\Type;
use CdnServices\GraphQL\Types\CdnImageType;

/**
 * GraphQL query: Tek bir CDN resmini getir
 */
class CdnImageQuery extends Query
{
    protected $attributes = [
        'name' => 'cdnImage',
        'description' => 'CDN Services\'teki tek bir resmi getirir',
    ];

    public function type(): Type
    {
        return \Rebing\GraphQL\Support\Facades\GraphQL::type('CdnImage');
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Resim ID veya path',
            ],
        ];
    }

    public function resolve($root, array $args)
    {
        $id = $args['id'];

        if (!Storage::disk('cdn-services')->exists($id)) {
            return null;
        }

        return CdnImageType::resolveFromArray([
            'id' => $id,
            'path' => $id,
            'url' => Storage::disk('cdn-services')->url($id),
            'size' => Storage::disk('cdn-services')->size($id),
            'mimeType' => Storage::disk('cdn-services')->mimeType($id),
            'lastModified' => Storage::disk('cdn-services')->lastModified($id),
        ]);
    }
}
