<?php

namespace CdnServices\GraphQL\Types;

use Illuminate\Support\Facades\Storage;
use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;

/**
 * GraphQL type for CDN Services image
 */
class CdnImageType extends GraphQLType
{
    protected $attributes = [
        'name' => 'CdnImage',
        'description' => 'CDN Services üzerindeki bir resim',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Resim ID',
            ],
            'path' => [
                'type' => Type::string(),
                'description' => 'Dosya yolu',
            ],
            'url' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Resim erişim URL',
            ],
            'size' => [
                'type' => Type::int(),
                'description' => 'Dosya boyutu (byte)',
            ],
            'mimeType' => [
                'type' => Type::string(),
                'description' => 'MIME tipi',
            ],
            'lastModified' => [
                'type' => Type::int(),
                'description' => 'Son değiştirilme zamanı (Unix timestamp)',
            ],
            'processedUrls' => [
                'type' => Type::listOf(Type::string()),
                'description' => 'İşlenmiş görsel URL\'leri (thumbnail, boyutlar)',
            ],
        ];
    }

    /**
     * Resolve field from array data (id, path, url, etc.)
     */
    public static function resolveFromArray(array $data): array
    {
        $disk = Storage::disk('cdn-services');
        $id = $data['id'] ?? $data['path'] ?? '';

        return [
            'id' => $id,
            'path' => $data['path'] ?? $id,
            'url' => $data['url'] ?? $disk->url($id),
            'size' => $data['size'] ?? ($disk->exists($id) ? $disk->size($id) : 0),
            'mimeType' => $data['mimeType'] ?? ($disk->exists($id) ? $disk->mimeType($id) : null),
            'lastModified' => $data['lastModified'] ?? ($disk->exists($id) ? $disk->lastModified($id) : null),
            'processedUrls' => array_values($data['processedUrls'] ?? self::defaultProcessedUrls($id)),
        ];
    }

    protected static function defaultProcessedUrls(string $id): array
    {
        $base = config('cdn-services.base_url', config('filesystems.disks.cdn-services.base_url', 'http://localhost:3012'));
        $base = rtrim($base, '/');
        return [
            'thumbnail' => "{$base}/api/image/{$id}/thumbnail/jpeg",
            'small' => "{$base}/api/image/{$id}/300x300/webp",
            'medium' => "{$base}/api/image/{$id}/800x800/webp",
            'large' => "{$base}/api/image/{$id}/1920x1080/webp",
        ];
    }
}
