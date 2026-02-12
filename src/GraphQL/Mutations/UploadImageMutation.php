<?php

namespace CdnServices\GraphQL\Mutations;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Rebing\GraphQL\Support\Mutation;
use GraphQL\Type\Definition\Type;
use CdnServices\GraphQL\Types\CdnImageType;

/**
 * GraphQL mutation: CDN Services'e resim yükle
 */
class UploadImageMutation extends Mutation
{
    protected $attributes = [
        'name' => 'uploadCdnImage',
        'description' => 'CDN Services\'e resim yükler',
    ];

    public function type(): Type
    {
        return \Rebing\GraphQL\Support\Facades\GraphQL::type('CdnImage');
    }

    public function args(): array
    {
        return [
            'file' => [
                'type' => Type::nonNull(\Rebing\GraphQL\Support\Facades\GraphQL::type('Upload')),
                'description' => 'Yüklenecek resim dosyası (multipart)',
            ],
            'path' => [
                'type' => Type::string(),
                'description' => 'Hedef klasör/yol (örn: images)',
                'defaultValue' => 'images',
            ],
            'disk' => [
                'type' => Type::string(),
                'description' => 'Hedef disk: local, s3, azure, gcs',
                'defaultValue' => 'local',
            ],
            'caption' => [
                'type' => Type::string(),
                'description' => 'Görüntü açıklaması',
            ],
            'tags' => [
                'type' => Type::listOf(Type::string()),
                'description' => 'Etiketler',
            ],
            'folder' => [
                'type' => Type::string(),
                'description' => 'Klasör adı',
            ],
            'visibility' => [
                'type' => Type::string(),
                'description' => 'public, private veya unlisted',
                'defaultValue' => 'public',
            ],
        ];
    }

    public function resolve($root, array $args)
    {
        $file = $args['file'];
        $pathPrefix = $args['path'] ?? 'images';
        $diskName = $args['disk'] ?? 'local';

        if (!$file instanceof UploadedFile) {
            throw new \InvalidArgumentException('Geçersiz dosya: Upload tipinde olmalı.');
        }

        $validator = \Illuminate\Support\Facades\Validator::make(
            ['file' => $file],
            [
                'file' => [
                    'required',
                    'image',
                    'max:' . (int) (config('cdn-services.image.max_size', 52428800) / 1024), // KB
                ],
            ],
            [
                'file.image' => 'Dosya resim formatında olmalı (jpeg, png, gif, webp, vb.).',
                'file.max' => 'Dosya boyutu izin verilen limiti aşamaz.',
            ]
        );

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

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
        $path = Storage::disk('cdn-services')->put($pathPrefix, $file, $options);

        if (!$path) {
            throw new \RuntimeException('CDN Services\'e yükleme başarısız.');
        }

        $url = Storage::disk('cdn-services')->url($path);
        $id = $path;
        if (preg_match('/^(.+)\/[^\/]+$/', $path, $m)) {
            // path "images/uuid" şeklinde dönebilir; id bazen sadece uuid
            $id = $path;
        }

        return CdnImageType::resolveFromArray([
            'id' => $id,
            'path' => $path,
            'url' => $url,
            'size' => Storage::disk('cdn-services')->size($path),
            'mimeType' => Storage::disk('cdn-services')->mimeType($path),
            'lastModified' => Storage::disk('cdn-services')->lastModified($path),
        ]);
    }
}
