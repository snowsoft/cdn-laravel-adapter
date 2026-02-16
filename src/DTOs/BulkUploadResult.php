<?php

declare(strict_types=1);

namespace CdnServices\DTOs;

final class BulkUploadResult
{
    /** @param array<int, array{id: string, path: string, originalName?: string}> $uploaded */
    /** @param array<int, array{path: string, error: string}> $failed */
    public function __construct(
        public readonly int $total,
        public readonly int $uploadedCount,
        public readonly int $failedCount,
        public readonly array $uploaded,
        public readonly array $failed,
    ) {}

    public function isFullySuccessful(): bool
    {
        return $this->failedCount === 0;
    }

    /** @return array<int, string> */
    public function uploadedIds(): array
    {
        return array_column($this->uploaded, 'id');
    }
}
