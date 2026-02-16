<?php

declare(strict_types=1);

namespace CdnServices\Services;

use CdnServices\Contracts\CdnApiClientInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class CdnApiClient implements CdnApiClientInterface
{
    public function __construct(
        protected string $baseUrl,
        protected ?string $apiKey = null,
        protected ?string $bearerToken = null,
        protected int $timeout = 60,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    protected function client(): PendingRequest
    {
        $client = Http::timeout($this->timeout)->baseUrl($this->baseUrl);
        if ($this->bearerToken !== null) {
            $client = $client->withToken($this->bearerToken);
        } elseif ($this->apiKey !== null) {
            $client = $client->withHeaders(['X-API-Key' => $this->apiKey]);
        }
        return $client;
    }

    public function upload(string $path, array $options = []): array
    {
        if (!is_file($path)) {
            return ['success' => false, 'error' => 'File not found: ' . $path];
        }
        $filename = $options['filename'] ?? basename($path);
        $contents = file_get_contents($path);
        return $this->uploadContents($contents, $filename, $options);
    }

    public function uploadContents(string $contents, string $originalName, array $options = []): array
    {
        $response = $this->client()->asMultipart()->attach(
            'file',
            $contents,
            $originalName
        )->post('/api/upload', [
            'bucket' => $options['bucket'] ?? null,
            'disk' => $options['bucket'] ?? null,
            'caption' => $options['caption'] ?? null,
            'tags' => isset($options['tags']) ? (is_array($options['tags']) ? implode(',', $options['tags']) : $options['tags']) : null,
            'folder' => $options['folder'] ?? null,
            'visibility' => $options['visibility'] ?? 'public',
        ]);

        if (!$response->successful()) {
            return [
                'success' => false,
                'error' => $response->json('error', $response->reason()),
                'message' => $response->json('message'),
            ];
        }
        return $response->json();
    }

    public function importBatch(array $urls, array $options = []): array
    {
        $urls = array_slice(array_values(array_filter(array_map('trim', $urls))), 0, 50);
        $response = $this->client()->post('/api/import/batch', [
            'urls' => $urls,
            'bucket' => $options['bucket'] ?? null,
            'disk' => $options['bucket'] ?? null,
        ]);
        if (!$response->successful()) {
            return [
                'success' => false,
                'imported' => 0,
                'failed' => count($urls),
                'importedItems' => [],
                'failedItems' => array_map(fn($u) => ['url' => $u, 'error' => $response->json('error', $response->reason())], $urls),
            ];
        }
        return $response->json();
    }

    public function bulkDelete(array $ids): array
    {
        $ids = array_values(array_filter($ids));
        $response = $this->client()->post('/api/images/bulk-delete', ['ids' => $ids]);
        if (!$response->successful()) {
            return [
                'success' => false,
                'deleted' => 0,
                'failed' => count($ids),
                'deletedIds' => [],
                'failedItems' => array_map(fn($id) => ['id' => $id, 'error' => $response->json('error', $response->reason())], $ids),
            ];
        }
        return $response->json();
    }

    public function request(string $method, string $uri, array $data = []): Response
    {
        $uri = str_starts_with($uri, '/') ? $uri : '/' . $uri;
        return $this->client()->{strtolower($method)}($uri, $data);
    }
}
