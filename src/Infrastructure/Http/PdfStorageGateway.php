<?php

namespace CdnServices\Infrastructure\Http;

use CdnServices\Domain\Pdf\PdfDocument;
use CdnServices\Domain\Pdf\PdfSession;
use CdnServices\Domain\Pdf\PdfStorageGatewayInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

/**
 * Infrastructure adapter: CDN Services backend /api/pdf/* (blockchain ledger, süreli session).
 */
class PdfStorageGateway implements PdfStorageGatewayInterface
{
    protected string $baseUrl;
    protected ?string $token;
    protected int $timeout;

    public function __construct(array $config = [])
    {
        $config = $config ?: config('cdn-services', config('filesystems.disks.cdn-services', []));
        $this->baseUrl = rtrim($config['base_url'] ?? 'http://localhost:3012', '/');
        $this->token = $config['token'] ?? null;
        $this->timeout = (int) ($config['timeout'] ?? 30);
    }

    public function upload(UploadedFile $file): ?PdfDocument
    {
        $req = Http::timeout($this->timeout);
        if ($this->token) {
            $req = $req->withToken($this->token);
        }
        $response = $req->attach(
            'file',
            $file->get(),
            $file->getClientOriginalName()
        )->post($this->baseUrl . '/api/pdf/upload');

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json('document');
        return $data ? PdfDocument::fromArray($data) : null;
    }

    /**
     * @return list<PdfDocument>
     */
    public function list(): array
    {
        $req = Http::timeout($this->timeout);
        if ($this->token) {
            $req = $req->withToken($this->token);
        }
        $response = $req->get($this->baseUrl . '/api/pdf');

        if (! $response->successful()) {
            return [];
        }

        $documents = $response->json('documents', []);
        return array_map(
            fn (array $d) => PdfDocument::fromArray($d),
            $documents
        );
    }

    public function createSession(string $documentId): ?PdfSession
    {
        $req = Http::timeout($this->timeout);
        if ($this->token) {
            $req = $req->withToken($this->token);
        }
        $response = $req->post($this->baseUrl . '/api/pdf/' . $documentId . '/session');

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        return PdfSession::fromArray($data);
    }

    public function delete(string $documentId): bool
    {
        $req = Http::timeout($this->timeout);
        if ($this->token) {
            $req = $req->withToken($this->token);
        }
        $response = $req->delete($this->baseUrl . '/api/pdf/' . $documentId);

        return $response->successful();
    }

    public function generateFromHtml(string $html, string $filename = 'generated.pdf'): ?PdfDocument
    {
        $req = Http::timeout($this->timeout * 2);
        if ($this->token) {
            $req = $req->withToken($this->token);
        }
        $response = $req->post($this->baseUrl . '/api/pdf/generate', [
            'html' => $html,
            'filename' => $filename,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json('document');
        return $data ? PdfDocument::fromArray($data) : null;
    }

    public function isEnabled(): bool
    {
        $req = Http::timeout(5);
        if ($this->token) {
            $req = $req->withToken($this->token);
        }
        $response = $req->get($this->baseUrl . '/api/pdf');

        return $response->status() !== 404;
    }
}
