<?php

namespace CdnServices\Domain\Pdf;

/**
 * Domain value object: PDF document metadata (resim alanından bağımsız).
 */
final class PdfDocument
{
    public function __construct(
        public readonly string $id,
        public readonly string $filename,
        public readonly ?string $originalName,
        public readonly int $size,
        public readonly string $mimetype,
        public readonly string $uploadedAt,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            filename: (string) ($data['filename'] ?? ''),
            originalName: isset($data['originalName']) ? (string) $data['originalName'] : null,
            size: (int) ($data['size'] ?? 0),
            mimetype: (string) ($data['mimetype'] ?? 'application/pdf'),
            uploadedAt: (string) ($data['uploadedAt'] ?? $data['uploaded_at'] ?? ''),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'originalName' => $this->originalName,
            'size' => $this->size,
            'mimetype' => $this->mimetype,
            'uploadedAt' => $this->uploadedAt,
        ];
    }
}
