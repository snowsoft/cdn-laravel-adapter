# CDN Services – GraphQL Desteği ve Resim Yükleme Eklentisi

Laravel projenizde CDN Services’i **GraphQL** ile kullanmak ve **resim yükleme** mutation’larını kullanmak için bu rehberi takip edin.

## Gereksinimler

- Laravel 9+
- CDN Services Laravel adapter kurulu ve yapılandırılmış ([INSTALLATION.md](INSTALLATION.md))
- CDN Services backend çalışır durumda ve bağlantı ayarları (`.env`: `CDN_SERVICES_BASE_URL`, `CDN_SERVICES_TOKEN`) doğru

## 1. GraphQL Paketini Kurun

[rebing/graphql-laravel](https://github.com/rebing/graphql-laravel) kullanın:

```bash
composer require rebing/graphql-laravel
php artisan vendor:publish --provider="Rebing\GraphQL\GraphQLServiceProvider"
```

## 2. CDN Services GraphQL Provider’ı Ekleyin

`config/app.php` içinde `providers` dizisine ekleyin:

```php
'providers' => [
    // ...
    CdnServices\CdnServicesServiceProvider::class,
    CdnServices\CdnServicesGraphQLServiceProvider::class,  // GraphQL eklentisi
],
```

## 3. GraphQL Şemasına Bağlama

`config/graphql.php` dosyanızda şema tanımına CDN Services query ve mutation’larını ekleyin.

**Örnek (tek default şema):**

```php
'schemas' => [
    'default' => [
        'query' => [
            'cdnImage' => \CdnServices\GraphQL\Queries\CdnImageQuery::class,
            'cdnImages' => \CdnServices\GraphQL\Queries\CdnImagesQuery::class,
            // ... kendi query'leriniz
        ],
        'mutation' => [
            'uploadCdnImage' => \CdnServices\GraphQL\Mutations\UploadImageMutation::class,
            'uploadCdnImageBase64' => \CdnServices\GraphQL\Mutations\UploadImageFromBase64Mutation::class,
            'deleteCdnImage' => \CdnServices\GraphQL\Mutations\DeleteImageMutation::class,
            // ... kendi mutation'larınız
        ],
        'types' => [
            'CdnImage' => \CdnServices\GraphQL\Types\CdnImageType::class,
            'Upload' => \CdnServices\GraphQL\Scalars\UploadScalar::class,
            // ... kendi type'larınız
        ],
    ],
],
```

Provider’ı eklediyseniz types/queries/mutations bazı sürümlerde otomatik merge edilebilir; yoksa yukarıdaki gibi manuel ekleyin.

## 4. Resim Yükleme: İki Yöntem

### A) Base64 ile (her ortamda çalışır)

Multipart desteği olmadan, tek istekte resmi base64 string olarak gönderirsiniz:

**Mutation:** `uploadCdnImageBase64` (opsiyonel: `caption`, `tags`, `folder`, `visibility`)

**Örnek istek (cURL / Postman):**

```json
{
  "query": "mutation Upload($image: String!, $path: String, $disk: String, $caption: String) { uploadCdnImageBase64(image: $image, path: $path, disk: $disk, caption: $caption) { id path url size mimeType processedUrls } }",
  "variables": {
    "image": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==",
    "path": "images",
    "disk": "local",
    "caption": "Açıklama"
  }
}
```

**Örnek JavaScript (Apollo/fetch):**

```javascript
const base64 = "data:image/png;base64,..."; // veya canvas.toDataURL()

const res = await fetch('/graphql', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    query: `
      mutation UploadCdnImageBase64($image: String!, $path: String, $disk: String) {
        uploadCdnImageBase64(image: $image, path: $path, disk: $disk) {
          id path url size mimeType processedUrls
        }
      }
    `,
    variables: { image: base64, path: 'images', disk: 'local' }
  })
});
```

### B) Multipart (Upload) ile

GraphQL multipart request spec kullanan istemciler (Apollo Upload Client, apollo-upload-client vb.) ile dosyayı doğrudan `Upload` tipinde gönderebilirsiniz.

**Mutation:** `uploadCdnImage`

- `file`: `Upload` (multipart ile gelen dosya)
- `path`: string (örn. `"images"`)
- `disk`: string (örn. `"local"`, `"s3"`)

**Örnek (Apollo Client + apollo-upload-client):**

```javascript
import { createUploadLink } from 'apollo-upload-client';

const uploadLink = createUploadLink({ uri: '/graphql' });

// Mutation
const [uploadCdnImage] = useMutation(gql`
  mutation UploadCdnImage($file: Upload!, $path: String, $disk: String) {
    uploadCdnImage(file: $file, path: $path, disk: $disk) {
      id path url size mimeType processedUrls
    }
  }
`);

await uploadCdnImage({
  variables: {
    file: selectedFile,  // File object
    path: 'images',
    disk: 'local'
  }
});
```

Sunucu tarafında multipart isteği işleyip `variables.file`’ı `UploadedFile` olarak resolver’a iletmeniz gerekir (rebing/graphql-laravel ve kullandığınız middleware’e göre yapılandırma farklı olabilir).

## 5. Query Örnekleri

**Tek resim:**

```graphql
query GetImage($id: String!) {
  cdnImage(id: $id) {
    id path url size mimeType lastModified processedUrls
  }
}
```

**Resim listesi:**

```graphql
query ListImages($directory: String) {
  cdnImages(directory: $directory) {
    id path url size mimeType lastModified
  }
}
```

## 6. Silme

```graphql
mutation DeleteCdnImage($id: String!) {
  deleteCdnImage(id: $id)
}
```

## 7. Bağlantı ve Güvenlik

- **Bağlantı:** CDN Services’e bağlantı, Laravel adapter üzerinden yapılır. GraphQL resolver’lar `Storage::disk('cdn-services')` kullanır; dolayısıyla `.env` içindeki `CDN_SERVICES_BASE_URL` ve `CDN_SERVICES_TOKEN` değerleri kullanılır.
- **Auth:** GraphQL endpoint’inizi `auth` middleware ile koruyun; resim yükleme/silme mutation’larını sadece giriş yapmış kullanıcılara açın.
- **Limit:** Resim boyutu `config('cdn-services.image.max_size')` (varsayılan 50MB) ile sınırlıdır.

## Özet

| Özellik            | Açıklama |
|--------------------|----------|
| **Bağlanma**       | CDN Services’e Laravel adapter + `.env` ile bağlanılır; GraphQL sadece aynı Storage disk’ini kullanır. |
| **Resim yükleme**  | `uploadCdnImage` (multipart) veya `uploadCdnImageBase64` (base64). |
| **Liste / tek**    | `cdnImages`, `cdnImage`. |
| **Silme**         | `deleteCdnImage`. |

Detaylı Laravel adapter kurulumu için [INSTALLATION.md](INSTALLATION.md) ve [README.md](README.md) dosyalarına bakın.
