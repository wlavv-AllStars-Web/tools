<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Models\prestashop\pack;
use App\Models\prestashop\product;
use App\Models\prestashop\specific_price;
use App\Models\prestashop\product_comment;
use App\Models\prestashop\stock_available;
use App\Models\prestashop\groupinc_configuration;

use App\Models\modules\dashboard\dashboard;


class dashboardController extends Controller
{
    
    public function index() { 
        
        dd('NO PERMISSIONS!');
        
    }
    
    public function cron_update() { 
        return dashboard::updateCounters();
    }
    
    public function getTabPanels($tab) { 
        return dashboard::getCountersList( $tab );
    }

    public function getCountersContent(Request $request){
        return response()->json([ 'html' => dashboard::getCountersContentOfTabPanel( $request->tab, $request->panel) ]);
    }  

}
