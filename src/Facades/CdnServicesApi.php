<?php

namespace CdnServices\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * CDN Services API Facade – meta, liste, usage (kota), signed URL, işlenmiş URL, import, placeholder.
 *
 * @method static array|null getInfo(string $id)
 * @method static array listImages(array $filters = [])
 * @method static bool updateMeta(string $id, array $patch)
 * @method static array|null replace(string $id, $file) Throws QuotaExceededException on 413
 * @method static array|null usage() Returns quotaBytes, quotaMB when backend has USER_STORAGE_QUOTA_BYTES
 * @method static int|null getQuotaBytes()
 * @method static int|null getQuotaRemaining()
 * @method static array|null importFromUrl(string $url) Throws QuotaExceededException on 413
 * @method static array|null createPlaceholder(array $options = []) Throws QuotaExceededException on 413
 * @method static array bulkDelete(array $ids)
 * @method static array|null getSignedUrl(string $id, int $expiresIn = 3600)
 * @method static string processedUrl(string $id, string $size, string $format, array $query = [])
 */
class CdnServicesApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'cdn-services.api';
    }
}
