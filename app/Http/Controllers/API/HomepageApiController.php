<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\homepageEditor\HomepageFrontendService;
use Illuminate\Http\Request;

class HomepageApiController extends Controller
{
    public function index(Request $request, HomepageFrontendService $service)
    {
        $lang = in_array($request->get('lang'), ['en', 'es', 'fr'], true) ? $request->get('lang') : 'en';

        return response()->json($service->getStructuredData($lang));
    }
}
