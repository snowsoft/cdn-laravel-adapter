<?php

namespace CdnServices\Facades;

use CdnServices\Application\Pdf\PdfStorageService;
use CdnServices\Domain\Pdf\PdfDocument;
use CdnServices\Domain\Pdf\PdfSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Facade;

/**
 * PDF Storage facade (DDD – Application service).
 *
 * @method static PdfDocument|null upload(UploadedFile $file)
 * @method static list<PdfDocument> list()
 * @method static PdfSession|null createSession(string $documentId)
 * @method static string sessionUrl(PdfSession $session)
 * @method static bool delete(string $documentId)
 * @method static PdfDocument|null generateFromHtml(string $html, string $filename = 'generated.pdf')
 * @method static bool isEnabled()
 *
 * @see PdfStorageService
 */
class CdnServicesPdf extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'cdn-services.pdf';
    }
}
