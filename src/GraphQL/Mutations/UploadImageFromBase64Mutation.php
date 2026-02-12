<?php

namespace CdnServices\GraphQL\Mutations;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Rebing\GraphQL\Support\Mutation;
use GraphQL\Type\Definition\Type;
use CdnServices\GraphQL\Types\CdnImageType;

/**
 * GraphQL mutation: Base64 ile CDN Services'e resim yükle
 * Multipart/Upload desteği olmayan ortamlarda kullanılabilir.
 */
class UploadImageFromBase64Mutation extends Mutation
{
    protected $attributes = [
        'name' => 'uploadCdnImageBase64',
        'description' => 'Base64 ile CDN Services\'e resim yükler (Upload tipi kullanılamıyorsa)',
    ];

    public function type(): Type
    {
        return \Rebing\GraphQL\Support\Facades\GraphQL::type('CdnImage');
    }

    public function args(): array
    {
        return [
            'image' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Base64 ile kodlanmış resim (data:image/xxx;base64,... veya ham base64)',
            ],
            'path' => [
                'type' => Type::string(),
                'description' => 'Hedef klasör/yol',
                'defaultValue' => 'images',
            ],
            'filename' => [
                'type' => Type::string(),
                'description' => 'Dosya adı (opsiyonel, yoksa otomatik üretilir)',
            ],
            'disk' => [
                'type' => Type::string(),
                'description' => 'Hedef disk: local, s3, azure, gcs',
                'defaultValue' => 'local',
            ],
            'caption' => [
                'type' => Type::string(),
            ],
            'tags' => [
                'type' => Type::listOf(Type::string()),
            ],
            'folder' => [
                'type' => Type::string(),
            ],
            'visibility' => [
                'type' => Type::string(),
                'defaultValue' => 'public',
            ],
        ];
    }

    public function resolve($root, array $args)
    {
        $imageData = $args['image'];
        $pathPrefix = $args['path'] ?? 'images';
        $filename = $args['filename'] ?? null;
        $diskName = $args['disk'] ?? 'local';

        // data:image/png;base64,... formatını ayıkla
        if (preg_match('/^data:image\/\w+;base64,(.+)$/', $imageData, $m)) {
            $imageData = $m[1];
        }

        $contents = base64_decode($imageData, true);
        if ($contents === false) {
            throw new \InvalidArgumentException('Geçersiz Base64 veri.');
        }

        $maxSize = config('cdn-services.image.max_size', 52428800);
        if (strlen($contents) > $maxSize) {
            throw new \InvalidArgumentException('Dosya boyutu limiti aşıldı (max ' . round($maxSize / 1024 / 1024) . ' MB).');
        }

        $ext = 'jpg';
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($contents);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        if (isset($allowed[$mime])) {
            $ext = $allowed[$mime];
        }
        $name = $filename ?: (Str::uuid()->toString() . '.' . $ext);
        $path = $pathPrefix . '/' . $name;

        $options = ['disk' => $diskName];
        if (!empty($args['caption'])) {
            $options['caption'] = $args['caption'];
        }
        if (!empty($args['tags'])) {
            $options['tags'] = $args['tags'];
        }
        if (!empty($args['folder'])) {
            $options['folder'] = $args['folder'];
        }
        if (!empty($args['visibility']) && in_array($args['visibility'], ['public', 'private', 'unlisted'], true)) {
            $options['visibility'] = $args['visibility'];
        }
        $put = Storage::disk('cdn-services')->put($path, $contents, $options);

        if (!$put) {
            throw new \RuntimeException('CDN Services\'e yükleme başarısız.');
        }

        $url = Storage::disk('cdn-services')->url($path);
        return CdnImageType::resolveFromArray([
            'id' => $path,
            'path' => $path,
            'url' => $url,
            'size' => strlen($contents),
            'mimeType' => $mime,
            'lastModified' => Storage::disk('cdn-services')->lastModified($path),
        ]);
    }
}
