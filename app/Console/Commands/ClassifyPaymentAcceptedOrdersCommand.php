<?php

namespace App\Console\Commands;

use App\Models\modules\picking\picking;
use Illuminate\Console\Command;

class ClassifyPaymentAcceptedOrdersCommand extends Command
{
    protected $signature = 'orders:classify-payment-accepted';

    protected $description = 'Move payment accepted orders to preparation or backorder according to available stock.';

    public function handle(): int
    {
        $summary = picking::classifyPaymentAcceptedOrders();

        $this->info(sprintf(
            'Payment accepted orders checked: %d. Preparation: %d. Backorder: %d.',
            $summary->checked,
            $summary->preparation,
            $summary->backorder
        ));

        return self::SUCCESS;
    }
}
