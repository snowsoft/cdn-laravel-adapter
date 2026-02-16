<?php

declare(strict_types=1);

namespace CdnServices\Commands;

use CdnServices\Contracts\CdnApiClientInterface;
use CdnServices\DTOs\BulkDeleteResult;
use Illuminate\Console\Command;

class CdnBulkDeleteCommand extends Command
{
    protected $signature = 'cdn:bulk-delete
                            {ids?* : Image ID list (or use --file= path to file, one ID per line)}
                            {--file= : Path to file containing image IDs (one per line)}';

    protected $description = 'Toplu silme: CDN Services POST /api/images/bulk-delete (sadece sizin kayıtlarınız).';

    public function handle(CdnApiClientInterface $client): int
    {
        $ids = $this->argument('ids');
        $file = $this->option('file');

        if ($file && is_string($file) && is_readable($file)) {
            $content = file_get_contents($file);
            $ids = array_filter(array_map('trim', explode("\n", $content)), fn($id) => $id !== '');
        } elseif (is_array($ids)) {
            $ids = array_values(array_filter($ids, fn($id) => is_string($id) && $id !== ''));
        } else {
            $ids = [];
        }

        if ($ids === []) {
            $this->error('En az bir image ID gerekli (argüman veya --file=).');
            return self::FAILURE;
        }

        $response = $client->bulkDelete($ids);
        $result = BulkDeleteResult::fromApiResponse($response);

        if ($result->deletedIds !== []) {
            $this->info('Silinen ID\'ler: ' . implode(', ', $result->deletedIds));
        }
        if ($result->failedItems !== []) {
            $this->warn('Başarısız: ' . $result->failed);
            $this->table(
                ['ID', 'Hata'],
                array_map(fn($f) => [$f['id'], $f['error']], $result->failedItems)
            );
        }

        $this->info(sprintf('Silme tamamlandı: %d silindi, %d hata.', $result->deleted, $result->failed));
        return $result->failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
