<?php

namespace App\Services\Marketing;

use App\Models\modules\marketing\NewsletterEmail;
use App\Services\Mail\StoreMailer;

class NewsletterEmailSenderService
{
    public function sendPending(int $limit = 10): array
    {
        $limit = max(1, $limit);

        $query = NewsletterEmail::query()
            ->where('sent', 0)
            ->where(function ($query) {
                $query->whereNull('attempts')
                    ->orWhere('attempts', '<', 3);
            })
            ->orderBy('id')
            ->limit($limit);

        if (app()->environment('local') || str_contains(strtolower(base_path()), 'xampp')) {
            $query->where('email', 'bruno.fernandes.asm@gmail.com');
        }

        $items = $query->get();
        $results = [];

        foreach ($items as $item) {
            try {
                StoreMailer::sendHtml('asm_media', $item->email, $item->subject, $item->html);

                $item->sent = 1;
                $item->attempts = (int) ($item->attempts ?? 0) + 1;
                $item->sent_at = now();
                $item->save();

                $results[] = [
                    'id' => $item->id,
                    'email' => $item->email,
                    'status' => 'sent',
                ];
            } catch (\Throwable $e) {
                $item->attempts = (int) ($item->attempts ?? 0) + 1;
                $item->save();

                $results[] = [
                    'id' => $item->id,
                    'email' => $item->email,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
