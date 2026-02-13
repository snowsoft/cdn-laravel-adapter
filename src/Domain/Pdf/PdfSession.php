<?php

namespace CdnServices\Domain\Pdf;

/**
 * Domain value object: süreli PDF erişim oturumu.
 */
final class PdfSession
{
    public function __construct(
        public readonly string $sessionToken,
        public readonly int $expiresAt,
        public readonly int $expiresIn,
        public readonly string $documentId,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            sessionToken: (string) ($data['sessionToken'] ?? ''),
            expiresAt: (int) ($data['expiresAt'] ?? 0),
            expiresIn: (int) ($data['expiresIn'] ?? 0),
            documentId: (string) ($data['documentId'] ?? ''),
        );
    }

    /**
     * Session ile dosyaya erişim URL'i (query parametreli).
     */
    public function url(string $baseUrl): string
    {
        $base = rtrim($baseUrl, '/');
        return $base . '/api/pdf/' . $this->documentId . '?session=' . urlencode($this->sessionToken);
    }
}
