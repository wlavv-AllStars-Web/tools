<?php
namespace App\Console\Commands;

use App\Services\StockAudit\StockAuditService;
use Illuminate\Console\Command;

class CreateStockAuditSnapshot extends Command
{
    protected $signature = 'stock-audit:snapshot';
    protected $description = 'Create a compressed stock and stock_arrive snapshot.';

    public function handle(StockAuditService $audit): int
    {
        $snapshot = $audit->createSnapshot();
        $this->info('Snapshot #'.$snapshot['id'].' created with '.$snapshot['items_count'].' rows.');
        return self::SUCCESS;
    }
}