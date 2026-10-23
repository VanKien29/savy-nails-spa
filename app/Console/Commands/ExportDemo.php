<?php

namespace App\Console\Commands;

use App\Services\StaticPreviewExporter;
use Illuminate\Console\Command;

class ExportDemo extends Command
{
    protected $signature = 'starter:export-demo';

    protected $description = 'Export explicitly approved public pages and assets to dist/preview with noindex and disabled forms';

    public function handle(StaticPreviewExporter $exporter): int
    {
        try {
            foreach ($exporter->export() as $path) {
                $this->info('Exported '.$path);
            }
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
        $this->info('Static preview ready in dist/preview. No server runtime or form persistence is included.');

        return self::SUCCESS;
    }
}
