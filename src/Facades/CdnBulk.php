<?php

declare(strict_types=1);

namespace CdnServices\Facades;

use CdnServices\Contracts\CdnBulkUploadServiceInterface;
use CdnServices\DTOs\BulkUploadResult;
use Illuminate\Support\Facades\Facade;

/**
 * DDD toplu yükleme servisi facade.
 *
 * @method static BulkUploadResult uploadMany(array $sources, array $defaults = [], ?array $perFile = null)
 *
 * @see CdnBulkUploadServiceInterface
 */
class CdnBulk extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CdnBulkUploadServiceInterface::class;
    }
}
