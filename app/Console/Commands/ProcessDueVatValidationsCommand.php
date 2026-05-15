<?php

namespace App\Console\Commands;

use App\Jobs\ValidateVatRequestJob;
use App\Models\modules\vat_validation_requests\vat_validation_requests;
use Illuminate\Console\Command;

class ProcessDueVatValidationsCommand extends Command
{
    protected $signature = 'vat:validate-due {--limit=25} {--sync : Run validations immediately without queue dispatch}';

    protected $description = 'Process pending/retry VAT validations that are due.';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $sync = (bool) $this->option('sync');

        $items = vat_validation_requests::query()
            ->readyToProcess()
            ->orderBy('next_attempt_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        foreach ($items as $item) {
            if ($sync) {
                app(ValidateVatRequestJob::class, ['vatValidationRequestId' => $item->id])->handle(
                    app(\App\Services\Vat\ViesVatService::class),
                    app(\App\Services\Vat\PrestashopVatCustomerService::class)
                );
            } else {
                ValidateVatRequestJob::dispatch($item->id);
            }
        }

        $this->info('VAT validations queued/processed: ' . $items->count());

        return self::SUCCESS;
    }
}
