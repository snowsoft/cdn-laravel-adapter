<?php

namespace CdnServices\Domain\Pdf;

use Illuminate\Http\UploadedFile;

/**
 * Domain port: PDF depolama (blockchain ledger, süreli session).
 * Resim alanından bağımsız; backend PDF_STORAGE_ENABLED ise kullanılır.
 */
interface PdfStorageGatewayInterface
{
    /**
     * PDF yükle; ledger'a kaydedilir.
     *
     * @return PdfDocument|null
     */
    public function upload(UploadedFile $file): ?PdfDocument;

    /**
     * Kullanıcının PDF listesi.
     *
     * @return list<PdfDocument>
     */
    public function list(): array;

    /**
     * Süreli erişim session'ı oluştur.
     */
    public function createSession(string $documentId): ?PdfSession;

    /**
     * PDF sil (sadece sahibi).
     */
    public function delete(string $documentId): bool;

    /**
     * PDF depolama backend'de aktif mi?
     */
    public function isEnabled(): bool;
}
