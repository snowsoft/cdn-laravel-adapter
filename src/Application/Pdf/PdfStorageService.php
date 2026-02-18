<?php

namespace CdnServices\Application\Pdf;

use CdnServices\Domain\Pdf\PdfDocument;
use CdnServices\Domain\Pdf\PdfSession;
use CdnServices\Domain\Pdf\PdfStorageGatewayInterface;
use Illuminate\Http\UploadedFile;

/**
 * Application service: PDF depolama use case'leri (Domain port kullanır).
 */
class PdfStorageService
{
    public function __construct(
        protected PdfStorageGatewayInterface $gateway
    ) {
    }

    public function upload(UploadedFile $file): ?PdfDocument
    {
        return $this->gateway->upload($file);
    }

    /**
     * @return list<PdfDocument>
     */
    public function list(): array
    {
        return $this->gateway->list();
    }

    public function createSession(string $documentId): ?PdfSession
    {
        return $this->gateway->createSession($documentId);
    }

    public function delete(string $documentId): bool
    {
        return $this->gateway->delete($documentId);
    }

    public function generateFromHtml(string $html, string $filename = 'generated.pdf'): ?PdfDocument
    {
        return $this->gateway->generateFromHtml($html, $filename);
    }

    public function isEnabled(): bool
    {
        return $this->gateway->isEnabled();
    }

    /**
     * Session ile erişim URL'i (base URL config'ten).
     */
    public function sessionUrl(PdfSession $session): string
    {
        $baseUrl = config('cdn-services.base_url', config('filesystems.disks.cdn-services.base_url', 'http://localhost:3012'));
        return $session->url($baseUrl);
    }
}
