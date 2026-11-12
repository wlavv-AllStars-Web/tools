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
        $panel = dashboard::query()
            ->where('tab', $request->tab)
            ->where('panel', $request->panel)
            ->when($request->filled('store'), fn ($query) => $query->where('store', $request->store))
            ->first();

        if (
            $request->tab === 'web'
            && $panel
            && dashboard::isCrossShopVatPanel($panel)
            && (int) auth()->id() !== 1003
        ) {
            abort(403);
        }

        return response()->json([
            'html' => dashboard::getCountersContentOfTabPanel(
                $request->tab,
                $request->panel,
                $request->store
            )
        ]);
    }  

}
