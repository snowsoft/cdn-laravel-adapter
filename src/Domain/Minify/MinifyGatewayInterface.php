<?php

declare(strict_types=1);

namespace CdnServices\Domain\Minify;

/**
 * Domain port: Minify servisi (JS/CSS sıkıştırma, publish, asset, ledger).
 * Backend /api/minify/* proxy ile aynı auth kullanır.
 */
interface MinifyGatewayInterface
{
    /**
     * Ham JS sıkıştır; sadece minified içerik döner (kaydetmez).
     *
     * @return string|null Minified JS veya hata durumunda null
     */
    public function minifyJs(string $content): ?string;

    /**
     * Ham CSS sıkıştır; sadece minified içerik döner (kaydetmez).
     *
     * @return string|null Minified CSS veya hata durumunda null
     */
    public function minifyCss(string $content): ?string;

    /**
     * JS yayınla: sıkıştır, ledger'a yaz, kalıcı asset döner.
     */
    public function publishJs(string $content): ?MinifyPublishResult;

    /**
     * CSS yayınla: sıkıştır, ledger'a yaz, kalıcı asset döner.
     */
    public function publishCss(string $content): ?MinifyPublishResult;

    /**
     * Yayınlanmış asset için tam URL (base_url + path).
     */
    public function assetUrl(string $assetId): string;

    /**
     * Ledger zincir doğrulama.
     */
    public function verifyLedger(): ?LedgerVerification;

    /**
     * Minify servisi yapılandırılmış ve erişilebilir mi?
     */
    public function isAvailable(): bool;
}
