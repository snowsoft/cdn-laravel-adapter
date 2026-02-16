<?php

declare(strict_types=1);

namespace CdnServices\DTOs;

final class BatchImportResult
{
    /** @param array<int, array{url: string, id: string}> $importedItems */
    /** @param array<int, array{url: string, error: string}> $failedItems */
    public function __construct(
        public readonly bool $success,
        public readonly int $imported,
        public readonly int $failed,
        public readonly array $importedItems,
        public readonly array $failedItems,
    ) {}

    public static function fromApiResponse(array $response): self
    {
        return new self(
            success: (bool) ($response['success'] ?? false),
            imported: (int) ($response['imported'] ?? 0),
            failed: (int) ($response['failed'] ?? 0),
            importedItems: $response['importedItems'] ?? [],
            failedItems: $response['failedItems'] ?? [],
        );
    }

    /** @return array<int, string> */
    public function importedIds(): array
    {
        return array_column($this->importedItems, 'id');
    }
}
