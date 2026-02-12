<?php

namespace CdnServices\GraphQL\Queries;

use Illuminate\Support\Facades\Storage;
use Rebing\GraphQL\Support\Query;
use GraphQL\Type\Definition\Type;
use CdnServices\GraphQL\Types\CdnImageType;

/**
 * GraphQL query: CDN Services'teki resimleri listele
 */
class CdnImagesQuery extends Query
{
    protected $attributes = [
        'name' => 'cdnImages',
        'description' => 'CDN Services\'teki resimleri listeler',
    ];

    public function type(): Type
    {
        return Type::listOf(\Rebing\GraphQL\Support\Facades\GraphQL::type('CdnImage'));
    }

    public function args(): array
    {
        return [
            'directory' => [
                'type' => Type::string(),
                'description' => 'Filtre için dizin (opsiyonel)',
                'defaultValue' => null,
            ],
        ];
    }

    public function resolve($root, array $args): array
    {
        $directory = $args['directory'] ?? null;
        $files = Storage::disk('cdn-services')->files($directory);

        $result = [];
        foreach ($files as $fileId) {
            $result[] = CdnImageType::resolveFromArray([
                'id' => $fileId,
                'path' => $fileId,
                'url' => Storage::disk('cdn-services')->url($fileId),
                'size' => Storage::disk('cdn-services')->size($fileId),
                'mimeType' => Storage::disk('cdn-services')->mimeType($fileId),
                'lastModified' => Storage::disk('cdn-services')->lastModified($fileId),
            ]);
        }

        return $result;
    }
}
