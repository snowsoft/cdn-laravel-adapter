<?php

declare(strict_types=1);

namespace CdnServices\Contracts;

use CdnServices\DTOs\BulkUploadResult;

interface CdnBulkUploadServiceInterface
{
    /**
     * Upload multiple files (e.g. from directory or list of paths).
     * Each file is sent as a separate POST /api/upload request.
     *
     * @param  array<int, string|\Illuminate\Http\UploadedFile>  $sources  File paths or UploadedFile instances
     * @param  array{ bucket?: string, caption?: string, tags?: string[], folder?: string, visibility?: string }  $defaults  Default metadata for all
     * @param  array<int, array{caption?: string, tags?: string[], folder?: string}>|null  $perFile  Optional per-file overrides (same index as $sources)
     */
    public function uploadMany(array $sources, array $defaults = [], ?array $perFile = null): BulkUploadResult;
}
