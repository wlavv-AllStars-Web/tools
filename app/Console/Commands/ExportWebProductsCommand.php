<?php

namespace App\Console\Commands;

use App\Services\Web\ProductExportService;
use Illuminate\Console\Command;

class ExportWebProductsCommand extends Command
{
    protected $signature = 'web:export-products';

    protected $description = 'Export all ASM products to a daily CSV file.';

    public function handle(ProductExportService $service): int
    {
        $result = $service->export();

        $this->info("Product export generated: {$result['filename']} ({$result['rows']} rows)");

        return self::SUCCESS;
    }
}
