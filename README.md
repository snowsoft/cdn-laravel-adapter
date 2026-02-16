# CDN Services Laravel Adapter

[![Latest Version on Packagist](https://img.shields.io/packagist/v/snowsoft/cdn-laravel-adapter.svg?style=flat-square)](https://packagist.org/packages/snowsoft/cdn-laravel-adapter)
[![Total Downloads](https://img.shields.io/packagist/dt/snowsoft/cdn-laravel-adapter.svg?style=flat-square)](https://packagist.org/packages/snowsoft/cdn-laravel-adapter)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/snowsoft/cdn-laravel-adapter?style=flat-square)](https://packagist.org/packages/snowsoft/cdn-laravel-adapter)

Laravel Storage adapter for CDN Services Node.js backend. Use CDN Services as a Laravel filesystem disk (Laravel 9, 10, 11, 12). **Domain Driven Design (DDD)** uyumlu yapı: Domain (value objects, port interfaces), Application (use case services), Infrastructure (HTTP gateway).

- **Packagist:** [packagist.org/packages/snowsoft/cdn-laravel-adapter](https://packagist.org/packages/snowsoft/cdn-laravel-adapter)
- **Source:** [github.com/snowsoft/cdn-laravel-adapter](https://github.com/snowsoft/cdn-laravel-adapter)

### Özellikler

| Özellik | Açıklama |
|--------|----------|
| **Storage disk** | `Storage::disk('cdn-services')` ile put/get/delete/url, caption, tags, folder, visibility |
| **CdnServicesAuth** | Kayıt (registration token), giriş, `tokenForUser` ile JWT |
| **Depolama kotası** | `QuotaExceededException`, `getQuotaRemaining()`, `usage()` ile quotaBytes/quotaMB |
| **CdnServicesPdf** | PDF yükleme, session ile süreli erişim, DDD (PdfDocument, PdfSession) |
| **CdnServicesApi** | Meta, list, usage, import, placeholder, signed URL, `processedUrl`, **Cloudflare** `cloudflareProcessedUrl` |
| **CdnApi / CdnBulk** | Toplu işlem: `CdnApi::upload`, `importBatch`, `bulkDelete`; `CdnBulk::uploadMany`; Artisan: `cdn:bulk-upload`, `cdn:import-urls`, `cdn:bulk-delete` |

---

## DDD yapısı

| Katman | Açıklama |
|--------|----------|
| **Domain** | `Domain\Pdf\PdfDocument`, `PdfSession` (value objects); `PdfStorageGatewayInterface` (port). |
| **Application** | `Application\Pdf\PdfStorageService` – PDF use case'leri (upload, list, session, delete). |
| **Infrastructure** | `Infrastructure\Http\PdfStorageGateway` – backend `/api/pdf/*` HTTP adapter. |
| **Bulk (Contracts/Services)** | `Contracts\CdnApiClientInterface`, `CdnBulkUploadServiceInterface`; `Services\CdnApiClient`, `CdnBulkUploadService`; DTOs: `BulkUploadResult`, `BatchImportResult`, `BulkDeleteResult`. |

Bağımlılık Domain → Application → Infrastructure yönünde; uygulama `PdfStorageGatewayInterface` ile tip bağımlılığı kurar, implementasyon ServiceProvider'da bağlanır.

---

## Toplu işlem (DDD: CdnApi, CdnBulk, Artisan)

**Servisler:** `CdnApiClientInterface` (upload, importBatch, bulkDelete), `CdnBulkUploadServiceInterface` (uploadMany). **Facades:** `CdnApi`, `CdnBulk` (alias'ları eklemek için `config/app.php` veya composer `extra.laravel.aliases` içine `CdnApi` → `CdnServices\Facades\CdnApi`, `CdnBulk` → `CdnServices\Facades\CdnBulk` ekleyin).

**Artisan komutları:**

```bash
# Toplu dosya yükleme (dizin veya tek dosya)
php artisan cdn:bulk-upload /path/to/products --bucket=local --folder=ürünler --tags=ürün,yeni

# URL listesinden import (--file= veya argüman)
php artisan cdn:import-urls --file=urls.txt --bucket=local

# Toplu silme
php artisan cdn:bulk-delete --file=ids.txt
```

**Kod örneği:**

```php
use CdnServices\Contracts\CdnBulkUploadServiceInterface;
use CdnServices\Facades\CdnApi;
use CdnServices\DTOs\BatchImportResult;
use CdnServices\DTOs\BulkDeleteResult;

// Toplu yükleme (controller'da inject)
$result = app(CdnBulkUploadServiceInterface::class)->uploadMany(
    ['/path/to/1.jpg', '/path/to/2.png'],
    ['bucket' => 'local', 'folder' => 'ürünler']
);
$result->uploadedIds();

// URL import
$res = CdnApi::importBatch(['https://example.com/1.jpg'], ['bucket' => 'local']);
$result = BatchImportResult::fromApiResponse($res);

// Toplu silme
$res = CdnApi::bulkDelete(['id1', 'id2']);
$result = BulkDeleteResult::fromApiResponse($res);
```

---

## Requirements

- PHP 8.0+
- Laravel 9.x, 10.x, 11.x or 12.x
- [CDN Services](https://github.com/snowsoft/cdn-services) Node.js backend

---

## Installation

Install via Composer (Packagist):

```bash
composer require snowsoft/cdn-laravel-adapter
```

Service provider and aliases are auto-discovered. Publish config:

```bash
php artisan vendor:publish --tag=cdn-services-config
```

### Install from GitHub (development)

Add the VCS repository to `composer.json`, then require the package:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/snowsoft/cdn-laravel-adapter"
        }
    ]
}
```

```bash
composer require snowsoft/cdn-laravel-adapter
```

### Manual install

1. Clone the repo into your project (e.g. `packages/cdn-laravel-adapter`).
2. Add PSR-4 autoload: `"CdnServices\\": "packages/cdn-laravel-adapter/src/"`.
3. Register `CdnServices\CdnServicesServiceProvider` in `config/app.php` or `bootstrap/providers.php`.
4. Run `php artisan vendor:publish --tag=cdn-services-config`.

---

## Configuration

### Environment (`.env`)

```env
CDN_SERVICES_BASE_URL=http://localhost:3012
CDN_SERVICES_TOKEN=your-jwt-token-here
# Backend'de REGISTRATION_TOKEN zorunluysa; kayıt için (CdnServicesAuth::register)
CDN_SERVICES_REGISTRATION_TOKEN=
CDN_SERVICES_DISK=local
CDN_SERVICES_DEFAULT_DISK=local
CDN_SERVICES_TIMEOUT=30
```

### Filesystem (`config/filesystems.php`)

```php
'disks' => [
    // ...
    'cdn-services' => [
        'driver' => 'cdn-services',
        'base_url' => env('CDN_SERVICES_BASE_URL', 'http://localhost:3012'),
        'token' => env('CDN_SERVICES_TOKEN'),
        'disk' => env('CDN_SERVICES_DISK', 'local'),
    ],
],
```

---

## Usage

### Storage facade

```php
use Illuminate\Support\Facades\Storage;

// Upload (optional: caption, tags, folder, visibility)
Storage::disk('cdn-services')->put('images/photo.jpg', $fileContents, [
    'caption' => 'Açıklama',
    'tags' => ['ürün', 'kampanya'],
    'folder' => 'galeri',
    'visibility' => 'public', // public|private|unlisted
]);

// Read, exists, delete, url, copy, move
$contents = Storage::disk('cdn-services')->get('images/photo.jpg');
$exists = Storage::disk('cdn-services')->exists('images/photo.jpg');
Storage::disk('cdn-services')->delete('images/photo.jpg');
$url = Storage::disk('cdn-services')->url('images/photo.jpg');
Storage::disk('cdn-services')->copy('images/photo.jpg', 'images/photo-copy.jpg');
Storage::disk('cdn-services')->move('images/photo.jpg', 'images/new-photo.jpg');
```

### CdnServicesAuth – kayıt, giriş, token

Backend'de kullanıcı kaydı (registration token zorunlu olabilir), giriş ve CDN işlemleri için JWT almak:

```php
use CdnServices\Facades\CdnServicesAuth;

// Kayıt (registration token config'ten veya 2. parametre ile)
$result = CdnServicesAuth::register([
    'email' => 'user@example.com',
    'password' => 'secret123',
    'name' => 'Ad Soyad',
]);
// $result['token'] → JWT (CDN_SERVICES_TOKEN olarak kullanılabilir)
// $result['user'] → id, email, name, role, createdAt

// Giriş
$result = CdnServicesAuth::login('user@example.com', 'secret123');
if ($result) {
    $jwt = $result['token'];
}

// Sunucu tarafında bir kullanıcı için JWT üret (CDN işlemleri için)
$tokenPayload = CdnServicesAuth::tokenForUser($userId, 'user@example.com', 'user');
$jwt = $tokenPayload['token'] ?? null;

// Kayıt için token zorunlu mu?
if (CdnServicesAuth::requiresRegistrationToken()) {
    // Config'te CDN_SERVICES_REGISTRATION_TOKEN tanımlı
}
```

### CdnServices facade

```php
use CdnServices\Facades\CdnServices;

CdnServices::put('images/photo.jpg', $fileContents);
$contents = CdnServices::get('images/photo.jpg');
$url = CdnServices::url('images/photo.jpg');
```

### Upload with `UploadedFile`

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

public function upload(Request $request)
{
    $request->validate(['image' => 'required|image|max:51200']); // 50MB

    $path = Storage::disk('cdn-services')->put('images', $request->file('image'));
    return response()->json([
        'success' => true,
        'path' => $path,
        'url' => Storage::disk('cdn-services')->url($path),
    ]);
}
```

### CdnServicesApi – meta, list, usage (kota), import, placeholder, signed URL, processed URL

```php
use CdnServices\Facades\CdnServicesApi;

$info = CdnServicesApi::getInfo($imageId);
$images = CdnServicesApi::listImages(['tag' => 'ürün', 'favorite' => true]);
CdnServicesApi::updateMeta($imageId, ['caption' => 'Yeni başlık', 'visibility' => 'private']);
CdnServicesApi::replace($imageId, $request->file('image'));
$usage = CdnServicesApi::usage(); // fileCount, totalSize, totalSizeMB, viewCountTotal, quotaBytes?, quotaMB?
$quotaRemaining = CdnServicesApi::getQuotaRemaining(); // null if no quota
$file = CdnServicesApi::importFromUrl('https://example.com/photo.jpg');
$file = CdnServicesApi::createPlaceholder(['width' => 800, 'height' => 600, 'text' => 'Placeholder']);
$result = CdnServicesApi::bulkDelete(['id1', 'id2']);
$signed = CdnServicesApi::getSignedUrl($imageId, 3600);
$url = CdnServicesApi::processedUrl($imageId, '800x600', 'webp', [
    'quality' => 80, 'fit' => 'cover', 'filter' => 'sepia', 'crop' => 'smart', 'watermark' => true,
]);
```

### Different backend disks (S3, Azure, etc.)

```php
Storage::disk('cdn-services')->put('images/photo.jpg', $fileContents, ['disk' => 's3']);
Storage::disk('cdn-services')->put('images/photo.jpg', $fileContents, ['disk' => 'azure']);
```

### Processed image URLs

```php
$originalUrl = Storage::disk('cdn-services')->url($imageId);
$thumb = CdnServicesApi::processedUrl($imageId, 'thumbnail', 'jpeg');
$custom = CdnServicesApi::processedUrl($imageId, '800x600', 'webp', ['quality' => 75, 'watermark' => true]);
```

### Cloudflare Image Resizing

Backend’de Cloudflare Image Resizing açıksa, edge’de resize/format/quality için URL alınır:

```php
$url = CdnServicesApi::cloudflareProcessedUrl($imageId, '800x600', 'webp', [
    'quality' => 80, 'fit' => 'cover', 'crop' => 'smart',
]);
// Backend GET /api/image/{id}/cloudflare-url ile aynı parametreler
```

Detay: CDN Services [docs/CLOUDFLARE_IMAGE_RESIZING.md](https://github.com/snowsoft/cdn-services/blob/main/docs/CLOUDFLARE_IMAGE_RESIZING.md).

### CdnServicesPdf – PDF depolama (blockchain, süreli session)

Backend'de `PDF_STORAGE_ENABLED=true` ise kullanılır; resim alanından bağımsızdır.

```php
use CdnServices\Facades\CdnServicesPdf;
use CdnServices\Domain\Pdf\PdfDocument;
use CdnServices\Domain\Pdf\PdfSession;

if (!CdnServicesPdf::isEnabled()) {
    return; // PDF storage kapalı
}

// Yükle (value object döner)
$doc = CdnServicesPdf::upload($request->file('pdf'));
if ($doc instanceof PdfDocument) {
    $id = $doc->id;
}

// Listele
$documents = CdnServicesPdf::list(); // list<PdfDocument>

// Süreli erişim session'ı al
$session = CdnServicesPdf::createSession($doc->id);
if ($session instanceof PdfSession) {
    $url = CdnServicesPdf::sessionUrl($session); // GET ile PDF açılır
    // veya: $session->url(config('cdn-services.base_url'));
}

// Sil
CdnServicesPdf::delete($doc->id);
```

**Constructor injection (DDD):** Uygulama sınıflarında port kullanmak için:

```php
use CdnServices\Domain\Pdf\PdfStorageGatewayInterface;

class MyController extends Controller
{
    public function __construct(
        private PdfStorageGatewayInterface $pdfStorage
    ) {}
}
```

---

## Customization

**Custom base URL:** set `base_url` in `config/filesystems.php` for the `cdn-services` disk.

**Dynamic token:** in your service provider, when extending the disk, get config and call `$adapter->setToken(auth()->user()->cdn_token)` (or your logic) before returning the adapter.

---

## Storage quota and exceptions

When the backend has **USER_STORAGE_QUOTA_BYTES** set, uploads (Storage `put`, API `replace`, `importFromUrl`, `createPlaceholder`) may return **413**. The adapter throws `CdnServices\Exceptions\QuotaExceededException` so you can catch and show a friendly message:

```php
use CdnServices\Exceptions\QuotaExceededException;

try {
    Storage::disk('cdn-services')->put('images/photo.jpg', $contents);
} catch (QuotaExceededException $e) {
    return back()->with('error', 'Depolama limitiniz doldu.');
}
```

`CdnServicesApi::usage()` includes `quotaBytes` and `quotaMB` when a quota is set; use `getQuotaRemaining()` to show remaining space.

---

## Troubleshooting

| Problem | Check |
|--------|------|
| Connection refused | Backend running: `curl http://localhost:3012/health` |
| Unauthorized | Valid JWT in `CDN_SERVICES_TOKEN`; create token via backend `/api/auth/token` |
| File not found | Use image ID (not path) when calling URL/meta APIs |
| QuotaExceededException | Backend `USER_STORAGE_QUOTA_BYTES` limit reached; free space or increase quota |

---

## API quick reference

**Storage `put()` options:** `disk`, `caption`, `tags`, `folder`, `visibility`

**CdnServicesAuth:** `register`, `login`, `tokenForUser`, `getRegistrationToken`, `requiresRegistrationToken`

**CdnServicesPdf (DDD):** `upload(UploadedFile)`, `list()`, `createSession(documentId)`, `sessionUrl(PdfSession)`, `delete(documentId)`, `isEnabled()`

**CdnServicesApi:** `getInfo`, `listImages`, `updateMeta`, `replace`, `usage`, `getQuotaBytes`, `getQuotaRemaining`, `importFromUrl`, `createPlaceholder`, `bulkDelete`, `getSignedUrl`, `processedUrl`, `cloudflareProcessedUrl`

**Disk methods:** `exists`, `get`, `put`, `delete`, `copy`, `move`, `size`, `lastModified`, `mimeType`, `url`, `temporaryUrl`, `readStream`, `writeStream`, `files`

---

## License

MIT
