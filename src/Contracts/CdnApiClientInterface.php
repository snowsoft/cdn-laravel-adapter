<?php

declare(strict_types=1);

namespace CdnServices\Contracts;

use Illuminate\Http\Client\Response;

interface CdnApiClientInterface
{
    /**
     * Upload a single file to CDN.
     *
     * @param  string  $path  Absolute path to file or file contents
     * @param  array{ bucket?: string, caption?: string, tags?: string[], folder?: string, visibility?: string, filename?: string }  $options
     * @return array{ success: bool, file?: array }
     */
    public function upload(string $path, array $options = []): array;

    /**
     * Upload from binary content (e.g. in-memory).
     *
     * @param  string  $contents  Raw file contents
     * @param  string  $originalName  Original filename for Content-Disposition
     * @param  array  $options  Same as upload()
     * @return array
     */
    public function uploadContents(string $contents, string $originalName, array $options = []): array;

    /**
     * Batch import from URLs (POST /api/import/batch).
     *
     * @param  array<int, string>  $urls  List of image URLs (max 50)
     * @param  array{ bucket?: string }  $options
     * @return array{ success: bool, imported: int, failed: int, importedItems: array, failedItems: array }
     */
    public function importBatch(array $urls, array $options = []): array;

    /**
     * Bulk delete images (POST /api/images/bulk-delete).
     *
     * @param  array<int, string>  $ids  Image IDs
     * @return array{ success: bool, deleted: int, failed: int, deletedIds: array, failedItems: array }
     */
    public function bulkDelete(array $ids): array;

    /**
     * Raw HTTP request to CDN API (for custom endpoints).
     */
    public function request(string $method, string $uri, array $data = []): Response;
}
