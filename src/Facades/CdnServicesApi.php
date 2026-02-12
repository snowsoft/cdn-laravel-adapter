<?php

namespace CdnServices\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * CDN Services API Facade – meta, liste, usage, signed URL, işlenmiş URL.
 *
 * @method static array|null getInfo(string $id)
 * @method static array listImages(array $filters = [])
 * @method static bool updateMeta(string $id, array $patch)
 * @method static array|null replace(string $id, $file)
 * @method static array|null usage()
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
