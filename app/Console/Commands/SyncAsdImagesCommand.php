<?php

namespace App\Console\Commands;

use App\Models\prestashop\AsdImage;
use Illuminate\Console\Command;

class SyncAsdImagesCommand extends Command
{
    protected $signature = 'asd-images:sync {--limit=0 : Maximum number of unverified references to check. Use 0 for all pending references} {--setup-only : Only create/rename the table and import new references}';

    protected $description = 'Sync and verify ASD product image references incrementally.';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $limit = $limit > 0 ? $limit : null;

        AsdImage::ensureTable();
        $inserted = AsdImage::syncProductReferences();
        $removed = AsdImage::deleteStaleReferences();

        if ((bool) $this->option('setup-only')) {
            $this->info("ASD image table ready. New references inserted: {$inserted}; stale references removed: {$removed}");

            return self::SUCCESS;
        }

        $verified = AsdImage::verifyPending($limit);

        $this->info(
            'ASD images sync completed. '
            . "Inserted: {$inserted}; "
            . "Removed: {$removed}; "
            . "Verified: {$verified['verified']}; "
            . "Found: {$verified['found']}; "
            . "Missing: {$verified['missing']}."
        );

        return self::SUCCESS;
    }
}
