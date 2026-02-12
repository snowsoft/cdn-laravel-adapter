# GitHub'a Yükleme (snowsoft/cdn-laravel-adapter)

Bu klasörü [https://github.com/snowsoft/cdn-laravel-adapter](https://github.com/snowsoft/cdn-laravel-adapter) reposuna göndermek için:

## 1. Repo boşsa ilk push

```bash
cd cdn-laravel-adapter
git init
git add .
git commit -m "Initial commit: CDN Services Laravel adapter"
git branch -M main
git remote add origin https://github.com/snowsoft/cdn-laravel-adapter.git
git push -u origin main
```

## 2. Ana projeden sadece adapter'ı ayrı repo olarak kullanıyorsanız

Ana repoda `cdn-laravel-adapter` bir alt klasörse ve onu kendi repo’nuzda tutacaksanız:

- `cdn-laravel-adapter` içinde ayrı bir git repo başlatıp sadece bu klasörü `snowsoft/cdn-laravel-adapter` remote’una push edebilirsiniz (yukarıdaki komutlar).
- Veya ana repoyu clone edip `git subtree split` / ayrı bir clone ile sadece `cdn-laravel-adapter` klasörünü yeni repo’ya taşıyabilirsiniz.

## 3. Laravel projesinde kullanım

Laravel projesinin `composer.json` dosyasına:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/snowsoft/cdn-laravel-adapter"
    }
],
"require": {
    "snowsoft/cdn-laravel-adapter": "^1.0"
}
```

Ardından:

```bash
composer update snowsoft/cdn-laravel-adapter
```

Paket Packagist’e eklenirse `repositories` satırına gerek kalmaz; sadece `composer require snowsoft/cdn-laravel-adapter` yeterli olur.
