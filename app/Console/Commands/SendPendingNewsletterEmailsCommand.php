<?php

namespace App\Console\Commands;

use App\Services\Marketing\NewsletterEmailSenderService;
use Illuminate\Console\Command;

class SendPendingNewsletterEmailsCommand extends Command
{
    protected $signature = 'newsletter:send-pending {--limit=10 : Maximum pending newsletter emails to send}';

    protected $description = 'Send pending ASM newsletter emails.';

    public function handle(NewsletterEmailSenderService $sender): int
    {
        $results = $sender->sendPending((int) $this->option('limit'));
        $sent = collect($results)->where('status', 'sent')->count();
        $failed = collect($results)->where('status', 'error')->count();

        $this->info("Newsletter emails processed: " . count($results) . "; Sent: {$sent}; Failed: {$failed}.");

        foreach ($results as $result) {
            if (($result['status'] ?? '') === 'error') {
                $this->warn("#{$result['id']} {$result['email']}: {$result['message']}");
            }
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
