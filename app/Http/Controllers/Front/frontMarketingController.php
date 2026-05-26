<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Marketing\NewsletterEmailSenderService;

class frontMarketingController extends Controller
{
    public function __construct()
    {
    }

    public function send(NewsletterEmailSenderService $sender)
    {
        return response()->json($sender->sendPending(1));
    }
}
