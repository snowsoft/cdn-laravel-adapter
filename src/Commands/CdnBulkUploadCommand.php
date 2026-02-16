<?php

declare(strict_types=1);

namespace CdnServices\Commands;

use CdnServices\Contracts\CdnBulkUploadServiceInterface;
use Illuminate\Console\Command;

class CdnBulkUploadCommand extends Command
{
    protected $signature = 'cdn:bulk-upload
                            {path? : Directory path or single file to upload}
                            {--bucket= : Bucket/disk name}
                            {--caption= : Default caption for all files}
                            {--tags= : Comma-separated tags}
                            {--folder= : Folder path}
                            {--visibility=public : public|private|unlisted}
                            {--extensions=jpg,jpeg,png,webp,gif : Allowed extensions when path is directory}
                            {--limit=0 : Max files to upload (0 = no limit)}';

    protected $description = 'Toplu ürün/görsel yükleme: dizin veya dosyayı CDN Services\'e gönderir (DDD servis kullanır).';

    public function handle(CdnBulkUploadServiceInterface $bulkUpload): int
    {
        $path = $this->argument('path');
        if (!$path || !is_string($path)) {
            $path = $this->ask('Yüklenecek dizin veya dosya yolu?');
        }
        if (!$path) {
            $this->error('Path gerekli.');
            return self::FAILURE;
        }

        $base = realpath($path);
        if ($base === false) {
            $this->error('Dosya veya dizin bulunamadı: ' . $path);
            return self::FAILURE;
        }

        $files = $this->collectFiles($base);
        if ($files === []) {
            $this->warn('Yüklenecek dosya yok.');
            return self::SUCCESS;
        }

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $files = array_slice($files, 0, $limit);
        }

        $extensions = array_map('trim', explode(',', (string) $this->option('extensions')));
        $files = array_filter($files, fn($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), $extensions, true));
        if ($files === []) {
            $this->warn('Seçilen uzantılara uyan dosya yok.');
            return self::SUCCESS;
        }

        $defaults = array_filter([
            'bucket' => $this->option('bucket'),
            'caption' => $this->option('caption'),
            'tags' => $this->option('tags') ? array_map('trim', explode(',', (string) $this->option('tags'))) : null,
            'folder' => $this->option('folder'),
            'visibility' => $this->option('visibility') ?: 'public',
        ], fn($v) => $v !== null && $v !== '');

        $this->info(sprintf('Toplam %d dosya yüklenecek.', count($files)));

        $result = $bulkUpload->uploadMany($files, $defaults);

        if ($result->uploaded !== []) {
            $this->table(
                ['ID', 'Yerel path'],
                array_map(fn($u) => [$u['id'], $u['path']], $result->uploaded)
            );
        }
        if ($result->failed !== []) {
            $this->warn('Başarısız: ' . $result->failedCount);
            $this->table(
                ['Path', 'Hata'],
                array_map(fn($f) => [$f['path'], $f['error']], $result->failed)
            );
        }

        $this->info(sprintf('Tamamlandı: %d yüklendi, %d hata.', $result->uploadedCount, $result->failedCount));
        return $result->failedCount === 0 ? self::SUCCESS : self::FAILURE;
    }

    /** @return array<int, string> */
    private function collectFiles(string $base): array
    {
        if (is_file($base)) {
            return [$base];
        }
        $files = [];
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($it as $fi) {
            if ($fi->isFile()) {
                $files[] = $fi->getPathname();
            }
        }
        sort($files);
        return $files;
    }
}
