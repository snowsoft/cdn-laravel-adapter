<?php

declare(strict_types=1);

namespace CdnServices\Commands;

use CdnServices\Contracts\CdnApiClientInterface;
use CdnServices\DTOs\BatchImportResult;
use Illuminate\Console\Command;

class CdnBatchImportUrlsCommand extends Command
{
    protected $signature = 'cdn:import-urls
                            {urls?* : URL list (or use --file= path to text file, one URL per line)}
                            {--file= : Path to file containing URLs (one per line)}
                            {--bucket= : Bucket/disk name}';

    protected $description = 'URL listesinden toplu import: CDN Services POST /api/import/batch (max 50 URL).';

    public function handle(CdnApiClientInterface $client): int
    {
        $urls = $this->argument('urls');
        $file = $this->option('file');

        if ($file && is_string($file) && is_readable($file)) {
            $content = file_get_contents($file);
            $urls = array_filter(array_map('trim', explode("\n", $content)), fn($u) => $u !== '' && filter_var($u, FILTER_VALIDATE_URL));
        } elseif (is_array($urls)) {
            $urls = array_values(array_filter($urls, fn($u) => is_string($u) && filter_var($u, FILTER_VALIDATE_URL)));
        } else {
            $urls = [];
        }

        if ($urls === []) {
            $this->error('En az bir geçerli URL gerekli (argüman veya --file=).');
            return self::FAILURE;
        }

        $options = array_filter(['bucket' => $this->option('bucket')], fn($v) => $v !== null && $v !== '');
        $response = $client->importBatch($urls, $options);
        $result = BatchImportResult::fromApiResponse($response);

        $this->table(
            ['URL', 'CDN ID'],
            array_map(fn($i) => [$i['url'], $i['id']], $result->importedItems)
        );
        if ($result->failedItems !== []) {
            $this->warn('Başarısız: ' . $result->failed);
            $this->table(
                ['URL', 'Hata'],
                array_map(fn($f) => [$f['url'], $f['error']], $result->failedItems)
            );
        }

        $this->info(sprintf('Import tamamlandı: %d başarılı, %d hata.', $result->imported, $result->failed));
        return $result->failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
