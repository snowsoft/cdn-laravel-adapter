<?php

declare(strict_types=1);

namespace CdnServices\Application\Minify;

use CdnServices\Domain\Minify\LedgerVerification;
use CdnServices\Domain\Minify\MinifyGatewayInterface;
use CdnServices\Domain\Minify\MinifyPublishResult;

/**
 * Application service: Minify use case'leri (Domain port kullanır).
 * JS/CSS sıkıştırma, publish (ledger + asset), ledger doğrulama.
 */
class MinifyService
{
    public function __construct(
        protected MinifyGatewayInterface $gateway
    ) {
    }

    /**
     * Ham JavaScript sıkıştır; yalnızca içerik döner (kaydetmez).
     */
    public function minifyJs(string $content): ?string
    {
        return $this->gateway->minifyJs($content);
    }

    /**
     * Ham CSS sıkıştır; yalnızca içerik döner (kaydetmez).
     */
    public function minifyCss(string $content): ?string
    {
        return $this->gateway->minifyCss($content);
    }

    /**
     * JS yayınla: sıkıştır → ledger'a yaz → asset URL döner.
     */
    public function publishJs(string $content): ?MinifyPublishResult
    {
        return $this->gateway->publishJs($content);
    }

    /**
     * CSS yayınla: sıkıştır → ledger'a yaz → asset URL döner.
     */
    public function publishCss(string $content): ?MinifyPublishResult
    {
        return $this->gateway->publishCss($content);
    }

    /**
     * Yayınlanmış asset için tam indirme/erişim URL'i.
     */
    public function assetUrl(string $assetId): string
    {
        return $this->gateway->assetUrl($assetId);
    }

    /**
     * Ledger zincir bütünlüğünü doğrula.
     */
    public function verifyLedger(): ?LedgerVerification
    {
        return $this->gateway->verifyLedger();
    }

    /**
     * Minify servisi kullanılabilir mi?
     */
    public function isAvailable(): bool
    {
        return $this->gateway->isAvailable();
    }
}
