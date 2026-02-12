# CDN Services Laravel Adapter

Laravel Storage adapter for CDN Services Node.js backend. This package allows you to use CDN Services as a Laravel filesystem disk.

**Kaynak:** [github.com/snowsoft/cdn-laravel-adapter](https://github.com/snowsoft/cdn-laravel-adapter)

## 📦 Kurulum

### Composer ile (GitHub)

Laravel projenizin `composer.json` dosyasına repository ekleyin, ardından paketi yükleyin:

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

Laravel 9–12 ile otomatik paket keşfi (provider/alias) desteklenir; gerekirse `config/app.php` içinde `CdnServices\CdnServicesServiceProvider` ekleyin.

Config yayınlama:

```bash
php artisan vendor:publish --tag=cdn-services-config
```

### Manuel kurulum

1. Bu repoyu klonlayıp Laravel projenizin `packages/` altına `cdn-laravel-adapter` olarak kopyalayın.
2. `composer.json` içinde `autoload.psr-4` ile `"CdnServices\\": "packages/cdn-laravel-adapter/src/"` ekleyin.
3. `config/app.php` (veya `bootstrap/providers.php`) içinde `CdnServices\CdnServicesServiceProvider::class` kaydedin.
4. `php artisan vendor:publish --tag=cdn-services-config` çalıştırın.

## ⚙️ Yapılandırma

### Environment Variables

`.env` dosyanıza ekleyin:

```env
CDN_SERVICES_BASE_URL=http://localhost:3012
CDN_SERVICES_TOKEN=your-jwt-token-here
CDN_SERVICES_DISK=local
CDN_SERVICES_DEFAULT_DISK=local
CDN_SERVICES_TIMEOUT=30
```

### Filesystem Config

`config/filesystems.php` dosyasına disk ekleyin:

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

## 🚀 Kullanım

### Storage Facade ile Kullanım

```php
use Illuminate\Support\Facades\Storage;

// Dosya yükle (opsiyonel: caption, tags, folder, visibility)
Storage::disk('cdn-services')->put('images/photo.jpg', $fileContents, [
    'caption' => 'Açıklama',
    'tags' => ['ürün', 'kampanya'],
    'folder' => 'galeri',
    'visibility' => 'public', // public|private|unlisted
]);

// Dosya oku
$contents = Storage::disk('cdn-services')->get('images/photo.jpg');

// Dosya var mı kontrol et
if (Storage::disk('cdn-services')->exists('images/photo.jpg')) {
    // ...
}

// Dosya sil
Storage::disk('cdn-services')->delete('images/photo.jpg');

// Dosya URL'i al
$url = Storage::disk('cdn-services')->url('images/photo.jpg');

// Dosya kopyala
Storage::disk('cdn-services')->copy('images/photo.jpg', 'images/photo-copy.jpg');

// Dosya taşı
Storage::disk('cdn-services')->move('images/photo.jpg', 'images/new-photo.jpg');
```

### CdnServices Facade ile Kullanım

```php
use CdnServices\Facades\CdnServices;

// Dosya yükle
CdnServices::put('images/photo.jpg', $fileContents);

// Dosya oku
$contents = CdnServices::get('images/photo.jpg');

// Dosya URL'i al
$url = CdnServices::url('images/photo.jpg');
```

### UploadedFile ile Kullanım

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

public function upload(Request $request)
{
    $request->validate([
        'image' => 'required|image|max:51200', // 50MB
    ]);

    $file = $request->file('image');
    $path = Storage::disk('cdn-services')->put('images', $file);
    
    $url = Storage::disk('cdn-services')->url($path);
    
    return response()->json([
        'success' => true,
        'path' => $path,
        'url' => $url,
    ]);
}
```

### CdnServicesApi – Meta, liste, usage, signed URL, işlenmiş URL

Backend’in yeni API’lerini kullanmak için `CdnServicesApi` facade’ini kullanın:

```php
use CdnServices\Facades\CdnServicesApi;

// Görüntü bilgisi (dominantColor, viewCount, visibility, placeholderDataUrl, suggestedSrcset)
$info = CdnServicesApi::getInfo($imageId);

// Liste (filtreler: tag, from, to, mime, favorite, folder)
$images = CdnServicesApi::listImages(['tag' => 'ürün', 'favorite' => true]);

// Meta güncelle (PATCH): caption, tags, favorite, folder, visibility
CdnServicesApi::updateMeta($imageId, ['caption' => 'Yeni başlık', 'visibility' => 'private']);

// Görüntü dosyasını değiştir (PUT replace)
CdnServicesApi::replace($imageId, $request->file('image'));

// Kullanım özeti (fileCount, totalSize, totalSizeMB, viewCountTotal)
$usage = CdnServicesApi::usage();

// Toplu silme
$result = CdnServicesApi::bulkDelete(['id1', 'id2']);

// Signed URL (süre saniye)
$signed = CdnServicesApi::getSignedUrl($imageId, 3600);

// İşlenmiş görüntü URL (quality, fit, blur, filter, crop, watermark vb.)
$url = CdnServicesApi::processedUrl($imageId, '800x600', 'webp', [
    'quality' => 80,
    'fit' => 'cover',
    'filter' => 'sepia',
    'crop' => 'smart',
    'watermark' => true,
]);
```

### Farklı Disk Kullanımı

```php
// S3 disk'e yükle
Storage::disk('cdn-services')->put('images/photo.jpg', $fileContents, [
    'disk' => 's3'
]);

// Azure disk'e yükle
Storage::disk('cdn-services')->put('images/photo.jpg', $fileContents, [
    'disk' => 'azure'
]);
```

### Image Processing ile Kullanım

CDN Services otomatik olarak görüntü işleme yapar. Farklı boyutlarda görüntü almak için:

```php
// Orijinal görüntü URL'i
$originalUrl = Storage::disk('cdn-services')->url('image-id');

// Thumbnail / işlenmiş URL (CdnServicesApi ile parametreli)
$thumbnailUrl = CdnServicesApi::processedUrl($imageId, 'thumbnail', 'jpeg');
$customUrl = CdnServicesApi::processedUrl($imageId, '800x600', 'webp', ['quality' => 75, 'watermark' => true]);

// Özel boyut URL'i
$customSizeUrl = 'http://localhost:3012/api/image/image-id/800x600/webp';
```

## 📝 Örnek Controller

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:51200',
        ]);

        $file = $request->file('image');
        $path = Storage::disk('cdn-services')->put('images', $file);
        
        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => Storage::disk('cdn-services')->url($path),
        ]);
    }

    public function show($id)
    {
        if (!Storage::disk('cdn-services')->exists($id)) {
            abort(404);
        }

        $contents = Storage::disk('cdn-services')->get($id);
        $mimeType = Storage::disk('cdn-services')->mimeType($id);

        return response($contents)
            ->header('Content-Type', $mimeType);
    }

    public function delete($id)
    {
        Storage::disk('cdn-services')->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully',
        ]);
    }
}
```

## 🔧 Özelleştirme

### Custom Base URL

```php
// config/filesystems.php
'cdn-services' => [
    'driver' => 'cdn-services',
    'base_url' => 'https://cdn.example.com',
    'token' => env('CDN_SERVICES_TOKEN'),
    'disk' => 's3',
],
```

### Token Yönetimi

Token'ı dinamik olarak ayarlamak için:

```php
// Service Provider'da
public function boot()
{
    Storage::extend('cdn-services', function ($app, $config) {
        $adapter = new CdnServicesFilesystemAdapter($config);
        
        // Token'ı dinamik olarak ayarla
        if (auth()->check()) {
            $adapter->setToken(auth()->user()->cdn_token);
        }
        
        return $adapter;
    });
}
```

## 🐛 Troubleshooting

### "Connection refused" Hatası

CDN Services backend'inin çalıştığından emin olun:

```bash
curl http://localhost:3012/health
```

### "Unauthorized" Hatası

Token'ın geçerli olduğundan emin olun. Token'ı yeniden oluşturun:

```bash
curl -X POST http://localhost:3012/api/auth/token \
  -H "Content-Type: application/json" \
  -d '{"userId": "123", "email": "user@example.com"}'
```

### Dosya Bulunamadı Hatası

CDN Services'te dosya ID'sinin doğru olduğundan emin olun. Path yerine ID kullanın.

## 📚 API Referansı

### Storage put() opsiyonları

- `disk` – Hedef disk (local, s3, azure, gcs)
- `caption` – Görüntü açıklaması
- `tags` – Etiket dizisi veya virgülle ayrılmış string
- `folder` – Klasör adı
- `visibility` – `public`, `private` veya `unlisted`

### CdnServicesApi (app('cdn-services.api') veya CdnServicesApi facade)

- `getInfo($id)` – Görüntü bilgisi (dominantColor, viewCount, visibility, placeholderDataUrl, suggestedSrcset)
- `listImages($filters)` – Liste (tag, from, to, mime, favorite, folder)
- `updateMeta($id, $patch)` – caption, tags, favorite, folder, visibility
- `replace($id, $file)` – Dosyayı değiştir (UploadedFile veya path)
- `usage()` – fileCount, totalSize, totalSizeMB, viewCountTotal
- `bulkDelete($ids)` – Toplu silme
- `getSignedUrl($id, $expiresIn)` – Süreli link
- `processedUrl($id, $size, $format, $query)` – İşlenmiş URL (quality, fit, filter, crop, watermark vb.)

### Storage / CdnServices disk metodları

- `exists($path)` - Dosya var mı kontrol et
- `get($path)` - Dosya içeriğini al
- `put($path, $contents, $options)` - Dosya kaydet
- `delete($path)` - Dosya sil
- `copy($from, $to)` - Dosya kopyala
- `move($from, $to)` - Dosya taşı
- `size($path)` - Dosya boyutu
- `lastModified($path)` - Son değiştirilme zamanı
- `mimeType($path)` - MIME type
- `url($path)` - Public URL
- `temporaryUrl($path, $expiration)` - Geçici URL
- `readStream($path)` - Stream oku
- `writeStream($path, $resource)` - Stream yaz
- `files($directory)` - Dosyaları listele

## 📄 Lisans

MIT License

