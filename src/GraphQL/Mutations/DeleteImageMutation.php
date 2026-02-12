<?php

namespace CdnServices\GraphQL\Mutations;

use Illuminate\Support\Facades\Storage;
use Rebing\GraphQL\Support\Mutation;
use GraphQL\Type\Definition\Type;

/**
 * GraphQL mutation: CDN Services'ten resim sil
 */
class DeleteImageMutation extends Mutation
{
    protected $attributes = [
        'name' => 'deleteCdnImage',
        'description' => 'CDN Services\'ten resim siler',
    ];

    public function type(): Type
    {
        return Type::nonNull(Type::boolean());
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Silinecek resmin ID veya path',
            ],
        ];
    }

    public function resolve($root, array $args): bool
    {
        $id = $args['id'];

        if (!Storage::disk('cdn-services')->exists($id)) {
            throw new \RuntimeException('Resim bulunamadı: ' . $id);
        }

        return Storage::disk('cdn-services')->delete($id);
    }
}
