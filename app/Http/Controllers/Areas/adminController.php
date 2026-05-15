<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use App\Models\prestashop\currency;
use App\Models\modules\dashboard\dashboard;

class adminController extends Controller{
    
    public $actions = [];
    public $breadcrumbs = [];
    
    public function __construct(){
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('administration'), 'url' => route('administration.index')];
    }

    public function index()
    {
        $data = [
            'rates' => $this->currencies(),
            'actions' => $this->actions,
            'counters' => dashboard::calculateAndGetCountersOfTab('admin'),
            'breadcrumbs' => $this->breadcrumbs,
            'accessList' => $this->accessList()
        ];

        return View::make('areas/administration/index')->with($data);
    }

    private function accessList(){
        return [
            ['name' => trans('messages.tasks'), 'url' => route('admin.tools.asg_tasks.index'), 'icon' => '<i style="font-size: 40px;" class="fa-solid fa-list-check"></i>'],
            ['name' => trans('messages.oms'), 'url' => route('admin.tools.oms.dashboard'), 'icon' => '<i style="font-size: 40px;" class="fa-solid fa-warehouse"></i>'],
            ['name' => trans('messages.COMPATS'), 'url' => route('admin.tools.compats.index'), 'icon' => '<i style="font-size: 40px;" class="fa-solid fa-boxes-packing"></i>']
        ];
    }

    private function currencies(){

        $targetShops = [
            config('shops.ASD.id') => config('shops.ASD.code'),
            config('shops.ASM.id') => config('shops.ASM.code'),
        ];
    
        $currencyMap = [
            'EUR' => [
                'purchase' => 'EURO',
                'sale'     => 'EUR - SALE',
                'fallback' => 'EURO',
            ],
            'USD' => [
                'purchase' => 'DOLLAR',
                'sale'     => 'USD - SALE',
                'fallback' => null,
            ],
            'GBP' => [
                'purchase' => 'POUND',
                'sale'     => 'GBP - SALE',
                'fallback' => null,
            ],
            'YEN' => [
                'purchase' => 'YEN',
                'sale'     => 'YEN - SALE',
                'fallback' => null,
            ],
        ];
    
        $byShop = currency::query()
            ->where('deleted', 0)
            ->with(['shops' => function ($query) use ($targetShops) {
                $query->whereIn('ps_currency_shop.id_shop', array_keys($targetShops));
            }])
            ->get()
            ->flatMap(function ($currency) {
                return $currency->shops->map(function ($shop) use ($currency) {
                    return [
                        'shop_id' => (int) $shop->id_shop,
                        'name' => $currency->name,
                        'rate' => (float) $shop->conversion_rate,
                    ];
                });
            })
            ->groupBy('shop_id');
    
        $result = [];
    
        foreach ($targetShops as $shopId => $shopCode) {
            $shopCurrencies = $byShop->get($shopId, collect())->pluck('rate', 'name');
            foreach ($currencyMap as $currencyCode => $config) {
                $purchase = $shopCurrencies[$config['purchase']] ?? null;
                $sale = $shopCurrencies[$config['sale']] ?? ($config['fallback'] ? ($shopCurrencies[$config['fallback']] ?? null) : null);
                $result[$shopCode][$currencyCode] = [ 'purchase' => $purchase, 'sale' => $sale ];
            }
        }
        return $result;
    }
}
