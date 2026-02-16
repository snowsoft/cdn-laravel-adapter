<?php

declare(strict_types=1);

namespace CdnServices\DTOs;

final class BulkDeleteResult
{
    /** @param array<int, string> $deletedIds */
    /** @param array<int, array{id: string, error: string}> $failedItems */
    public function __construct(
        public readonly bool $success,
        public readonly int $deleted,
        public readonly int $failed,
        public readonly array $deletedIds,
        public readonly array $failedItems,
    ) {}

    public static function fromApiResponse(array $response): self
    {
        return new self(
            success: (bool) ($response['success'] ?? false),
            deleted: (int) ($response['deleted'] ?? 0),
            failed: (int) ($response['failed'] ?? 0),
            deletedIds: $response['deletedIds'] ?? [],
            failedItems: $response['failedItems'] ?? [],
        );
    }
}
