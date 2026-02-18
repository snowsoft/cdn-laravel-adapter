<?php

declare(strict_types=1);

namespace CdnServices\Infrastructure\Http;

use CdnServices\Domain\Minify\LedgerVerification;
use CdnServices\Domain\Minify\MinifyGatewayInterface;
use CdnServices\Domain\Minify\MinifyPublishResult;
use Illuminate\Support\Facades\Http;

/**
 * Infrastructure adapter: CDN Services backend /api/minify/* (JS/CSS sıkıştırma, publish, ledger).
 */
class MinifyGateway implements MinifyGatewayInterface
{
    protected string $baseUrl;
    protected ?string $token;
    protected ?string $apiKey;
    protected int $timeout;

    public function __construct(array $config = [])
    {
        $config = $config ?: config('cdn-services', config('filesystems.disks.cdn-services', []));
        $this->baseUrl = rtrim($config['base_url'] ?? 'http://localhost:3012', '/');
        $this->token = $config['token'] ?? $config['bearer_token'] ?? null;
        $this->apiKey = $config['api_key'] ?? null;
        $this->timeout = (int) ($config['timeout'] ?? 30);
    }

    protected function client(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::timeout($this->timeout)->baseUrl($this->baseUrl);
        if ($this->token !== null) {
            $client = $client->withToken($this->token);
        } elseif ($this->apiKey !== null) {
            $client = $client->withHeaders(['X-API-Key' => $this->apiKey]);
        }
        return $client;
    }

    public function minifyJs(string $content): ?string
    {
        $response = $this->client()
            ->withBody($content, 'application/javascript')
            ->post('/api/minify/js');

        if (! $response->successful()) {
            return null;
        }
        return $response->body();
    }

    public function minifyCss(string $content): ?string
    {
        $response = $this->client()
            ->withBody($content, 'text/css')
            ->post('/api/minify/css');

        if (! $response->successful()) {
            return null;
        }
        return $response->body();
    }

    public function publishJs(string $content): ?MinifyPublishResult
    {
        $response = $this->client()
            ->withBody($content, 'application/javascript')
            ->post('/api/minify/publish/js');

        if (! $response->successful()) {
            return null;
        }
        $data = $response->json();
        return $data ? MinifyPublishResult::fromArray($data) : null;
    }

    public function publishCss(string $content): ?MinifyPublishResult
    {
        $response = $this->client()
            ->withBody($content, 'text/css')
            ->post('/api/minify/publish/css');

        if (! $response->successful()) {
            return null;
        }
        $data = $response->json();
        return $data ? MinifyPublishResult::fromArray($data) : null;
    }

    public function assetUrl(string $assetId): string
    {
        return $this->baseUrl . '/api/minify/asset/' . $assetId;
    }

    public function verifyLedger(): ?LedgerVerification
    {
        $response = $this->client()->get('/api/minify/ledger/verify');
        if (! $response->successful()) {
            return null;
        }
        $data = $response->json();
        return $data ? LedgerVerification::fromArray($data) : null;
    }

    public function isAvailable(): bool
    {
        $response = $this->client()->get('/api/minify/ledger/verify');
        return $response->status() !== 503;
    }
}
