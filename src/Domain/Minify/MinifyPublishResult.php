<?php

declare(strict_types=1);

namespace CdnServices\Domain\Minify;

/**
 * Domain value object: Minify publish yanıtı (JS/CSS sıkıştırılıp ledger'a yazıldıktan sonra).
 */
final class MinifyPublishResult
{
    public function __construct(
        public readonly string $assetId,
        public readonly string $url,
        public readonly string $kind,
        public readonly int $size,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            assetId: (string) ($data['assetId'] ?? ''),
            url: (string) ($data['url'] ?? ''),
            kind: (string) ($data['kind'] ?? 'js'),
            size: (int) ($data['size'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'assetId' => $this->assetId,
            'url' => $this->url,
            'kind' => $this->kind,
            'size' => $this->size,
        ];
    }
}
