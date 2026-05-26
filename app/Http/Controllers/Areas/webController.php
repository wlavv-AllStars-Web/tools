<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use App\Models\modules\dashboard\dashboard;
use Throwable;

class webController extends Controller{
    
    public $breadcrumbs = [];
    
    public function __construct(){
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('web'), 'url' => route('web.index')];
    }

    public function index(){

        $data = [
            'counters'      => dashboard::calculateAndGetCountersOfTab('web'),
            'breadcrumbs'   => $this->breadcrumbs,
            'accessList'    => $this->accessList()
        ];

        return View::make('areas/web/index')->with($data);
    }

    public function sendPendingNewsletterEmails(): RedirectResponse
    {
        try {
            Artisan::call('newsletter:send-pending', ['--limit' => 10]);

            $output = trim(Artisan::output());

            return redirect()
                ->route('web.index')
                ->with('success', $output !== '' ? $output : 'Newsletter emails processed.');
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('web.index')
                ->with('error', 'Newsletter emails sending failed: ' . $e->getMessage());
        }
    }
    
    private function accessList(){
        
        $accessList = array();
        $accessList[]                           = ['name' =>  trans('messages.logs'),               'url' => route('logs.index'),                       'icon' => '<i style="font-size: 40px;" class="fa fa-history" aria-hidden="true"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.trackingTranslation'),'url' => route('web.tools.tracking.index'),         'icon' => '<i style="font-size: 40px;" class="fa-solid fa-language"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.seoComparator'),      'url' => route('web.tools.seo.index'),              'icon' => '<i style="font-size: 40px;" class="fa-solid fa-not-equal"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.rawText'),            'url' => route('web.tools.raw_text.index'),         'icon' => '<i style="font-size: 40px;" class="fa-solid fa-not-equal"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.changesTracker'),     'url' => route('web.tools.changes.index'),          'icon' => '<i style="font-size: 40px;" class="fa-solid fa-code"></i>'];
        $accessList[]                           = ['name' =>  'Migration tool',                     'url' => route('web.tools.db_migration.index'),     'icon' => '<i style="font-size: 40px;" class="fa-solid fa-database"></i>'];

        /**
        $accessList[]                           = ['name' =>  trans('messages.purchasePrice'),      'url' => route('purchasePrice.index'),              'icon' => '<i style="font-size: 40px;" class="fa-solid fa-money-bill-transfer"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.basePrice'),        'url' => route('basePrice.index'),                  'icon' => '<i style="font-size: 40px;" class="fa-solid fa-eur"></i>'];
        **/
        
        return $accessList;
    }
}
