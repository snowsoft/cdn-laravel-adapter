<?php

declare(strict_types=1);

namespace CdnServices\Domain\Minify;

/**
 * Domain value object: Minify ledger zincir doğrulama sonucu.
 */
final class LedgerVerification
{
    public function __construct(
        public readonly bool $valid,
        public readonly string $message,
        public readonly int $entries,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            valid: (bool) ($data['valid'] ?? false),
            message: (string) ($data['message'] ?? ''),
            entries: (int) ($data['entries'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'valid' => $this->valid,
            'message' => $this->message,
            'entries' => $this->entries,
        ];
    }
}
