<?php

namespace CdnServices;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

/**
 * CDN Services API client – yeni özellikler (meta, liste filtreleri, usage, signed URL, işlenmiş URL).
 */
class CdnServicesApi
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

    protected function request(string $method, string $endpoint, array $options = []): \Illuminate\Http\Client\Response
    {
        $url = $this->baseUrl . $endpoint;
        $request = Http::timeout($this->timeout);
        if ($this->token) {
            $request = $request->withToken($this->token);
        }
        if (!empty($options['multipart'])) {
            return $request->asMultipart()->send($method, $url, $options);
        }
        return $request->send($method, $url, $options);
    }

    /**
     * Görüntü bilgisi (info) – dominantColor, viewCount, visibility, placeholderDataUrl, suggestedSrcset dahil.
     */
    public function getInfo(string $id): ?array
    {
        $response = $this->request('get', "/api/info/{$id}");
        return $response->successful() ? $response->json() : null;
    }

    /**
     * Kendi görüntü listesi – filtreler: tag, from, to, mime, favorite, folder.
     */
    public function listImages(array $filters = []): array
    {
        $query = http_build_query(array_filter($filters));
        $endpoint = '/api/images' . ($query ? '?' . $query : '');
        $response = $this->request('get', $endpoint);
        if (!$response->successful()) {
            return [];
        }
        $data = $response->json();
        return $data['images'] ?? [];
    }

    /**
     * Meta güncelle (PATCH): caption, tags, favorite, folder, visibility.
     */
    public function updateMeta(string $id, array $patch): bool
    {
        $req = Http::timeout($this->timeout);
        if ($this->token) {
            $req = $req->withToken($this->token);
        }
        $response = $req->patch($this->baseUrl . "/api/image/{$id}", $patch);
        return $response->successful();
    }

    /**
     * Görüntü dosyasını değiştir (PUT replace). $file: UploadedFile veya dosya yolu.
     */
    public function replace(string $id, $file): ?array
    {
        if ($file instanceof UploadedFile) {
            $contents = $file->get();
            $filename = $file->getClientOriginalName();
        } elseif (is_string($file) && is_readable($file)) {
            $contents = file_get_contents($file);
            $filename = basename($file);
        } else {
            return null;
        }
        $request = Http::timeout($this->timeout);
        if ($this->token) {
            $request = $request->withToken($this->token);
        }
        $response = $request->attach('image', $contents, $filename)->put($this->baseUrl . "/api/image/{$id}");
        return $response->successful() ? $response->json() : null;
    }

    /**
     * Kullanım özeti: fileCount, totalSize, totalSizeMB, viewCountTotal.
     */
    public function usage(): ?array
    {
        $response = $this->request('get', '/api/usage');
        return $response->successful() ? $response->json() : null;
    }

    /**
     * Toplu silme (bulk delete).
     */
    public function bulkDelete(array $ids): array
    {
        $req = Http::timeout($this->timeout);
        if ($this->token) {
            $req = $req->withToken($this->token);
        }
        $response = $req->post($this->baseUrl . '/api/images/bulk-delete', ['ids' => $ids]);
        return $response->successful() ? $response->json() : ['success' => false, 'deleted' => 0, 'failed' => count($ids)];
    }

    /**
     * Signed URL oluştur (süre saniye).
     */
    public function getSignedUrl(string $id, int $expiresIn = 3600): ?array
    {
        $response = $this->request('get', "/api/image/{$id}/signed-url?expiresIn=" . $expiresIn);
        return $response->successful() ? $response->json() : null;
    }

    /**
     * İşlenmiş görüntü URL'i – quality, fit, blur, sharpen, grayscale, filter, rotate, strip, crop, watermark.
     */
    public function processedUrl(string $id, string $size, string $format, array $query = []): string
    {
        $params = array_filter([
            'quality' => $query['quality'] ?? null,
            'fit' => $query['fit'] ?? null,
            'blur' => $query['blur'] ?? null,
            'sharpen' => $query['sharpen'] ?? null,
            'grayscale' => isset($query['grayscale']) ? (int)(bool)$query['grayscale'] : null,
            'filter' => $query['filter'] ?? null,
            'rotate' => $query['rotate'] ?? null,
            'strip' => isset($query['strip']) ? (int)(bool)$query['strip'] : null,
            'crop' => $query['crop'] ?? null,
            'watermark' => isset($query['watermark']) ? (int)(bool)$query['watermark'] : null,
            'signature' => $query['signature'] ?? null,
            'expires' => $query['expires'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
        $queryString = $params ? '?' . http_build_query($params) : '';
        return $this->baseUrl . "/api/image/{$id}/{$size}/{$format}" . $queryString;
    }
}
