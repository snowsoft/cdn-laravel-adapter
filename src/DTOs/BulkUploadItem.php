<?php

declare(strict_types=1);

namespace CdnServices\DTOs;

final class BulkUploadItem
{
    public function __construct(
        public readonly string $pathOrKey,
        public readonly ?string $caption = null,
        /** @var array<int, string>|null */
        public readonly ?array $tags = null,
        public readonly ?string $folder = null,
        public readonly string $visibility = 'public',
    ) {}

    public static function fromPath(string $path, ?string $caption = null, ?array $tags = null, ?string $folder = null, string $visibility = 'public'): self
    {
        return new self($path, $caption, $tags, $folder, $visibility);
    }

    /** @return array{caption?: string, tags?: string[], folder?: string, visibility?: string} */
    public function toApiMeta(): array
    {
        $meta = [];
        if ($this->caption !== null) {
            $meta['caption'] = $this->caption;
        }
        if ($this->tags !== null) {
            $meta['tags'] = $this->tags;
        }
        if ($this->folder !== null) {
            $meta['folder'] = $this->folder;
        }
        if ($this->visibility !== 'public') {
            $meta['visibility'] = $this->visibility;
        }
        return $meta;
    }
}
