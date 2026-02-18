<?php

declare(strict_types=1);

namespace CdnServices\Facades;

use CdnServices\Application\Minify\MinifyService;
use CdnServices\Domain\Minify\LedgerVerification;
use CdnServices\Domain\Minify\MinifyPublishResult;
use Illuminate\Support\Facades\Facade;

/**
 * Minify facade (DDD – Application service).
 *
 * @method static string|null minifyJs(string $content)
 * @method static string|null minifyCss(string $content)
 * @method static MinifyPublishResult|null publishJs(string $content)
 * @method static MinifyPublishResult|null publishCss(string $content)
 * @method static string assetUrl(string $assetId)
 * @method static LedgerVerification|null verifyLedger()
 * @method static bool isAvailable()
 *
 * @see MinifyService
 */
class CdnMinify extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'cdn-services.minify';
    }
}
