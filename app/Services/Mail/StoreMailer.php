<?php

namespace App\Services\Mail;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class StoreMailer
{
    private const LOCAL_TEST_EMAIL = 'bruno.fernandes.asm@gmail.com';

    public static function mailerKeyForStore(string $storeCode): string
    {
        return strtoupper($storeCode) === 'ASD' ? 'asd_sales' : 'asm_sales';
    }

    public static function sendHtml(string $mailerKey, string $email, string $subject, string $html): void
    {
        $recipient = self::recipient($email);
        $from = self::configure($mailerKey);

        Mail::html($html, function ($message) use ($recipient, $subject, $from) {
            $message
                ->from($from['address'], $from['name'])
                ->to($recipient)
                ->subject($subject);
        });
    }

    public static function sendRaw(string $mailerKey, string $email, string $subject, string $body): void
    {
        $recipient = self::recipient($email);
        $from = self::configure($mailerKey);

        Mail::raw($body, function ($message) use ($recipient, $subject, $from) {
            $message
                ->from($from['address'], $from['name'])
                ->to($recipient)
                ->subject($subject);
        });
    }

    private static function recipient(string $email): string
    {
        return (app()->environment('local') || str_contains(strtolower(base_path()), 'xampp'))
            ? self::LOCAL_TEST_EMAIL
            : $email;
    }

    private static function configure(string $mailerKey): array
    {
        $config = (array) config('allstars.mailers.' . $mailerKey, []);

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', $config['host'] ?? config('mail.mailers.smtp.host'));
        Config::set('mail.mailers.smtp.port', $config['port'] ?? config('mail.mailers.smtp.port'));
        Config::set('mail.mailers.smtp.encryption', $config['encryption'] ?? config('mail.mailers.smtp.encryption'));
        Config::set('mail.mailers.smtp.username', $config['username'] ?? config('mail.mailers.smtp.username'));
        Config::set('mail.mailers.smtp.verify_peer', $config['verify_peer'] ?? !(app()->environment('local') || str_contains(strtolower(base_path()), 'xampp')));

        if (!empty($config['password'])) {
            Config::set('mail.mailers.smtp.password', $config['password']);
        }

        $from = [
            'address' => (string) ($config['from_address'] ?? config('mail.from.address')),
            'name' => (string) ($config['from_name'] ?? config('mail.from.name')),
        ];

        Config::set('mail.from.address', $from['address']);
        Config::set('mail.from.name', $from['name']);

        $mailManager = app('mail.manager');
        if (method_exists($mailManager, 'purge')) {
            $mailManager->purge('smtp');
        }

        return $from;
    }
}
