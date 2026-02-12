# CDN Services Laravel Adapter

[![Latest Version on Packagist](https://img.shields.io/packagist/v/snowsoft/cdn-laravel-adapter.svg?style=flat-square)](https://packagist.org/packages/snowsoft/cdn-laravel-adapter)
[![Total Downloads](https://img.shields.io/packagist/dt/snowsoft/cdn-laravel-adapter.svg?style=flat-square)](https://packagist.org/packages/snowsoft/cdn-laravel-adapter)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/snowsoft/cdn-laravel-adapter?style=flat-square)](https://packagist.org/packages/snowsoft/cdn-laravel-adapter)

Laravel Storage adapter for CDN Services Node.js backend. Use CDN Services as a Laravel filesystem disk (Laravel 9, 10, 11, 12).

- **Packagist:** [packagist.org/packages/snowsoft/cdn-laravel-adapter](https://packagist.org/packages/snowsoft/cdn-laravel-adapter)
- **Source:** [github.com/snowsoft/cdn-laravel-adapter](https://github.com/snowsoft/cdn-laravel-adapter)

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

**CdnServicesApi:** `getInfo`, `listImages`, `updateMeta`, `replace`, `usage`, `getQuotaBytes`, `getQuotaRemaining`, `importFromUrl`, `createPlaceholder`, `bulkDelete`, `getSignedUrl`, `processedUrl`

**Disk methods:** `exists`, `get`, `put`, `delete`, `copy`, `move`, `size`, `lastModified`, `mimeType`, `url`, `temporaryUrl`, `readStream`, `writeStream`, `files`

---

## License

MIT
