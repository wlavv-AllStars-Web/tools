<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\prestashop\asm_newsletter_email;
use App\Services\Mail\StoreMailer;

class frontMarketingController extends Controller
{
    public function __construct()
    {
    }

    public function send()
    {
        $query = asm_newsletter_email::query();

        if (app()->environment('local') || str_contains(strtolower(base_path()), 'xampp')) {
            $query->where('email', 'bruno.fernandes.asm@gmail.com');
        }

        $data = $query->latest('id')->take(1)->get();

        $results = [];

        foreach ($data as $item) {
            try {
                StoreMailer::sendHtml('asm_media', $item->email, $item->subject, $item->html);

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
