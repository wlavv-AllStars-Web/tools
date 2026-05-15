<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\prestashop\asm_newsletter_email;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class frontMarketingController extends Controller
{
    public function __construct()
    {
    }

    public function send()
    {
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailer', 'smtp');

        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', config('allstars.mailers.marketing.host'));
        Config::set('mail.mailers.smtp.port', config('allstars.mailers.marketing.port'));
        Config::set('mail.mailers.smtp.encryption', config('allstars.mailers.marketing.encryption'));
        Config::set('mail.mailers.smtp.username', config('allstars.mailers.marketing.username'));
        Config::set('mail.mailers.smtp.password', config('allstars.mailers.marketing.password'));

        Config::set('mail.from.address', config('allstars.mailers.marketing.from_address'));
        Config::set('mail.from.name', config('allstars.mailers.marketing.from_name'));

        $data = asm_newsletter_email::take(1)->get();

        $results = [];

        foreach ($data as $item) {
            try {
                Mail::send([], [], function ($message) use ($item) {
                    $message->to($item->email)
                        ->subject($item->subject)
                        ->html($item->html);
                });

                $results[] = [
                    'email' => $item->email,
                    'status' => 'sent',
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'email' => $item->email,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json($results);
    }
}
