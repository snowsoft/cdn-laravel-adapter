<?php

declare(strict_types=1);

namespace CdnServices\Services;

use CdnServices\Contracts\CdnApiClientInterface;
use CdnServices\Contracts\CdnBulkUploadServiceInterface;
use CdnServices\DTOs\BulkUploadResult;
use Illuminate\Http\UploadedFile;

class CdnBulkUploadService implements CdnBulkUploadServiceInterface
{
    public function __construct(
        protected CdnApiClientInterface $client
    ) {
    }

    public function uploadMany(array $sources, array $defaults = [], ?array $perFile = null): BulkUploadResult
    {
        $uploaded = [];
        $failed = [];
        $bucket = $defaults['bucket'] ?? null;
        $visibility = $defaults['visibility'] ?? 'public';

        foreach ($sources as $index => $source) {
            $overrides = $perFile[$index] ?? [];
            $caption = $overrides['caption'] ?? $defaults['caption'] ?? null;
            $tags = $overrides['tags'] ?? $defaults['tags'] ?? null;
            $folder = $overrides['folder'] ?? $defaults['folder'] ?? null;

            $pathOrKey = $this->resolvePath($source);
            if ($pathOrKey === null) {
                $failed[] = ['path' => is_string($source) ? $source : 'UploadedFile', 'error' => 'Invalid source or file not found'];
                continue;
            }

            $options = array_filter([
                'bucket' => $bucket,
                'caption' => $caption,
                'tags' => $tags,
                'folder' => $folder,
                'visibility' => $visibility,
            ], fn($v) => $v !== null && $v !== '');

            if ($source instanceof UploadedFile) {
                $result = $this->client->uploadContents(
                    $source->get(),
                    $source->getClientOriginalName(),
                    $options
                );
            } else {
                $result = $this->client->upload($pathOrKey, $options);
            }

            if (!empty($result['success']) && !empty($result['file']['id'])) {
                $uploaded[] = [
                    'id' => $result['file']['id'],
                    'path' => $pathOrKey,
                    'originalName' => $result['file']['originalName'] ?? null,
                ];
            } else {
                $failed[] = [
                    'path' => $pathOrKey,
                    'error' => $result['error'] ?? $result['message'] ?? 'Upload failed',
                ];
            }
        }

        return new BulkUploadResult(
            total: count($sources),
            uploadedCount: count($uploaded),
            failedCount: count($failed),
            uploaded: $uploaded,
            failed: $failed,
        );
    }

    /** @param string|UploadedFile $source */
    private function resolvePath($source): ?string
    {
        if ($source instanceof UploadedFile) {
            return $source->getRealPath() ?: $source->getClientOriginalName();
        }
        if (is_string($source) && is_file($source)) {
            return $source;
        }
        return null;
    }
}
