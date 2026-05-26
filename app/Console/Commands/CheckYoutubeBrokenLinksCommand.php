<?php

namespace App\Console\Commands;

use App\Services\Prestashop\YoutubeBrokenLinkSyncService;
use Illuminate\Console\Command;

class CheckYoutubeBrokenLinksCommand extends Command
{
    protected $signature = 'youtube:check-broken-links {--limit=0 : Maximum number of product/video references to check. Use 0 for all}';

    protected $description = 'Check Prestashop product YouTube codes and populate the broken YouTube links dashboard table.';

    public function handle(YoutubeBrokenLinkSyncService $service): int
    {
        $limit = (int) $this->option('limit');
        $limit = $limit > 0 ? $limit : null;

        $result = $service->sync($limit);

        $this->info(
            'YouTube broken links check completed. '
            . "References: {$result['references']}; "
            . "Videos checked: {$result['checked']}; "
            . "Working: {$result['working']}; "
            . "Broken videos: {$result['broken_codes']}; "
            . "Dashboard rows: {$result['broken_rows']}."
        );

        return self::SUCCESS;
    }
}
