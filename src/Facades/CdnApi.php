<?php

declare(strict_types=1);

namespace CdnServices\Facades;

use CdnServices\Contracts\CdnApiClientInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Facade;

/**
 * CDN Services API client facade (upload, import/batch, bulk-delete, raw request).
 *
 * @method static array upload(string $path, array $options = [])
 * @method static array uploadContents(string $contents, string $originalName, array $options = [])
 * @method static array importBatch(array $urls, array $options = [])
 * @method static array bulkDelete(array $ids)
 * @method static Response request(string $method, string $uri, array $data = [])
 *
 * @see CdnApiClientInterface
 */
class CdnApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CdnApiClientInterface::class;
    }
}
