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
        $accessList[]                           = ['name' => trans('messages.cars'),         'url' => route('marketing.tools.car_gallery.index'),  'icon' => '<i style="font-size: 40px;" class="fa-solid fa-car"></i>'];
        $accessList[]                           = ['name' => trans('messages.asg_events'),   'url' => route('asg_events.index'),                   'icon' => '<i style="font-size: 40px;" class="fa-solid fa-calendar-days"></i>'];
        $accessList[]                           = ['name' => trans('messages.homepage'),     'url' => route('marketing.tools.homepage.asm.index'), 'icon' => '<i style="font-size: 40px;" class="fa-solid fa-house-laptop"></i>'];
        $accessList[]                           = ['name' => trans('messages.homepage_asd'), 'url' => route('marketing.tools.homepage.asd.index'), 'icon' => '<i style="font-size: 40px;" class="fa-solid fa-house-laptop"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.logs'),               'url' => route('logs.index'),                       'icon' => '<i style="font-size: 40px;" class="fa fa-history" aria-hidden="true"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.trackingTranslation'),'url' => route('web.tools.tracking.index'),         'icon' => '<i style="font-size: 40px;" class="fa-solid fa-language"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.changesTracker'),     'url' => route('web.tools.changes.index'),          'icon' => '<i style="font-size: 40px;" class="fa-solid fa-code"></i>'];

        /**
        $accessList[]                           = ['name' =>  trans('messages.purchasePrice'),      'url' => route('purchasePrice.index'),              'icon' => '<i style="font-size: 40px;" class="fa-solid fa-money-bill-transfer"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.basePrice'),        'url' => route('basePrice.index'),                  'icon' => '<i style="font-size: 40px;" class="fa-solid fa-eur"></i>'];
        **/
        
        return $accessList;
    }
}
