<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;


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
        return response()->json([
            'html' => dashboard::getCountersContentOfTabPanel(
                $request->tab,
                $request->panel,
                $request->store
            )
        ]);
    }  

}
