<?php

namespace App\Models\modules\dashboard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Config;
use App\Services\Prestashop\PrestashopAdminLinkService;

use App\Models\prestashop\asm_dashboard;
use App\Models\prestashop\AsdImage;
use App\Models\prestashop\orders;
use App\Models\prestashop\product;
use App\Models\prestashop\product_shop;

use App\Models\Concerns\BuildsDashboardPanels;

class dashboard extends Model
{
    use HasFactory, BuildsDashboardPanels;
    protected $table = "dashboard";

    /** TO UPDATE COUNTERS **/
    public static function updateCountersOFTab( $tab ){ 
        
        $counters = array();
        
        $panels = dashboard::where('tab', $tab)->get();
    
        foreach($panels AS $panel){
            
            [$class, $method] = explode('::', $panel->function);
            $fullClass = $class === 'ASM_ukoo_customer'
                ? \App\Models\prestashop\asm_ukoo_customer::class
                : "App\\Models\\prestashop\\" . $class;
            $alternativeClass  = "App\\Models\\modules\\" . $class . "\\" . $class;

            if (class_exists($fullClass) && method_exists($fullClass, $method)) {
                $callableClass = $fullClass;
            } elseif (class_exists($alternativeClass) && method_exists($alternativeClass, $method)) {
                $callableClass = $alternativeClass;
            } else {
                \Log::warning("Método {$method} não encontrado nem em {$fullClass} nem em {$alternativeClass}");
                continue;
            }
            
            $counters[$panel->panel] = (object)$result = call_user_func([$callableClass, $method], 'counter', self::getCountersOFTabPanel( $tab, $panel->panel ));
            
            dashboard::where('panel', $panel->panel)->update(['counter' => $result['counter']]);
        }
        
        return $counters;
    }

    public static function creatTab( $tab, $panel, $function ){
        
        $tab_result = self::existTab( $tab, $panel );
        
        if( !isset($tab_result->id) ){
          
            $new = new dashboard();
            $new->tab = $tab;
            $new->panel = $panel;
            $new->function = $function;
            $new->counter = 0;
            $new->created_at = date('Y-m-d h:s:i');
            $new->updated_at = date('Y-m-d h:s:i');
            $new->save();
        
            return $new->id;
        }
        
        return $tab_result->id;
    }

    public static function existTab( $tab, $panel ){
        return dashboard::where('tab', $tab)->where('panel', $panel)->first();
    }

    public static function updateCounters( ){
        
        $counters = self::updateCountersOFTab( 'admin' );
        $counters = self::updateCountersOFTab( 'support' );
        $counters = self::updateCountersOFTab( 'finance' );
        $counters = self::updateCountersOFTab( 'logistics' );
        $counters = self::updateCountersOFTab( 'marketing' );
        $counters = self::updateCountersOFTab( 'sales' );
        
        return 1;
    }

    public static function getCountersList( $tab ){
        $panels = dashboard::select('tab', 'panel', 'counter')->where('tab', $tab)->get();
        return View::make('customTools/dashboard/list_header', compact('panels'));
    }
    
    /** Obtem os contadores e calcula no click do painel **/
    public static function getCountersOFTab( $tab ){ 
        
        $counters = dashboard::where('tab', $tab)->orderBy('store')->get();
        $asm = dashboard::where('tab', $tab)->where('store', 'ASM')->get();
        $asd = dashboard::where('tab', $tab)->where('store', 'ASD')->get();

        return View::make('areas/dashboard/includes/counters_header', compact('asm', 'asd'));
    }

    /** Calcula os contadores em tempo real **/
    public static function calculateAndGetCountersOfTab($tab, array $deferredPanels = [])
    {
        $panels = dashboard::where('tab', $tab)
            ->orderBy('store')
            ->get();
    
        $asm = collect();
        $asd = collect();
    
        foreach ($panels as $panel) {
            $counter = (int) $panel->counter;
            $error = null;
            $calculated = false;

            if (in_array($panel->panel, $deferredPanels, true)) {
                $item = clone $panel;
                $item->counter = $counter;
                $item->calculated = false;
                $item->error = null;

                if ($item->store === 'ASM') {
                    $asm->push($item);
                }

                if ($item->store === 'ASD') {
                    $asd->push($item);
                }

                continue;
            }
    
            [$modelClass, $method] = self::resolveDashboardCallable($panel->function ?? null);
    
            if ($modelClass && $method && method_exists($modelClass, $method)) {
                try {
                    $result = self::callDashboardMethod($modelClass, $method, $panel);
                    $counter = (int) ($result['counter'] ?? 0);
                    $calculated = true;
                } catch (\Throwable $e) {
                    $counter = 0;
                    $error = $e->getMessage();

                    \Log::error('Dashboard counter failed: ' . ($panel->function ?? 'null'), [
                        'tab' => $panel->tab,
                        'panel' => $panel->panel,
                        'store' => $panel->store,
                        'error' => $error,
                    ]);
                }
            } else {
                $error = 'Dashboard method not found: ' . ($panel->function ?? 'null');

                \Log::warning($error, [
                    'tab' => $panel->tab,
                    'panel' => $panel->panel,
                    'store' => $panel->store,
                ]);
            }
    
            if ((int) $panel->counter !== $counter) {
                $panel->counter = $counter;
                $panel->save();
            }
    
            $item = clone $panel;
            $item->counter = $counter;
            $item->calculated = $calculated;
            $item->error = $error;
    
            if ($item->store === 'ASM') {
                $asm->push($item);
            }
    
            if ($item->store === 'ASD') {
                $asd->push($item);
            }
        }
    
        return View::make('areas/dashboard/includes/counters_header', compact('asm', 'asd'));
    }
    
    protected static function callDashboardMethod($modelClass, $method, $panel)
    {
        $reflection = new \ReflectionMethod($modelClass, $method);
        $count = $reflection->getNumberOfParameters();
    
        return match ($count) {
            0 => $modelClass::$method(),
            1 => $modelClass::$method('counter'),
            2 => $modelClass::$method($panel->tab, $panel),
            3 => $modelClass::$method($panel->tab, $panel->store, $panel),
            default => $modelClass::$method($panel),
        };
    }

    protected static function callDashboardContentMethod($modelClass, $method, $panel)
    {
        $reflection = new \ReflectionMethod($modelClass, $method);
        $count = $reflection->getNumberOfParameters();

        return match ($count) {
            0 => $modelClass::$method(),
            1 => $modelClass::$method('counter'),
            2 => $modelClass::$method($panel->tab, $panel),
            3 => $modelClass::$method($panel->tab, $panel->store, $panel),
            default => $modelClass::$method($panel),
        };
    }

    protected static function resolveDashboardCallable($callable): array
    {
        if (!$callable) {
            return [null, null];
        }

        if (!str_contains($callable, '::')) {
            return [self::class, $callable];
        }

        [$model, $method] = explode('::', $callable, 2);

        $knownModels = [
            'dashboard' => self::class,
            'product' => \App\Models\prestashop\product::class,
            'orders' => \App\Models\prestashop\orders::class,
            'customer' => \App\Models\prestashop\customer::class,
            'specific_price' => \App\Models\prestashop\specific_price::class,
            'product_comment' => \App\Models\prestashop\product_comment::class,
            'pack' => \App\Models\prestashop\pack::class,
            'cart_rules' => \App\Models\prestashop\cart_rules::class,
            'asm_ukoo_customer' => \App\Models\prestashop\asm_ukoo_customer::class,
            'ASM_ukoo_customer' => \App\Models\prestashop\asm_ukoo_customer::class,
        ];

        if (isset($knownModels[$model])) {
            return [$knownModels[$model], $method];
        }

        $candidates = [
            $model,
            "App\\Models\\prestashop\\{$model}",
            "App\\Models\\modules\\{$model}\\{$model}",
        ];

        foreach ($candidates as $candidate) {
            if (class_exists($candidate)) {
                return [$candidate, $method];
            }
        }

        return [null, $method];
    }

    public static function getCountersOFTabPanel( $tab, $panel ){ 
        return dashboard::where('tab', $tab)->where('panel', $panel)->value('counter');
    }

    public static function getCountersContentOfTabPanel( $tab, $panel_name ){
        
        $panel = dashboard::where('tab', $tab)->where('panel', $panel_name)->first();

        if (!$panel) {
            \Log::warning("Dashboard panel not found for tab {$tab} and panel {$panel_name}");

            return [
                'counter' => 0,
                'update_tag' => 0,
                'error' => true,
                'message' => trans('messages.Panel not found'),
                'html' => '<div id="' . e($panel_name) . '" data-open="1" class="panel-body alert alert-danger">Panel not found.</div>',
            ];
        }

        [$modelClass, $method] = self::resolveDashboardCallable($panel->function ?? null);

        if (!$modelClass || !$method || !method_exists($modelClass, $method)) {
            \Log::warning('Dashboard method not found: ' . ($panel->function ?? 'null'));

            return [
                'counter' => (int) $panel->counter,
                'update_tag' => 0,
                'error' => true,
                'message' => trans('messages.Panel method not found'),
                'html' => '<div id="' . e($panel->panel) . '" data-open="1" class="panel-body alert alert-danger">Panel method not found.</div>',
            ];
        }

        try {
            $result = self::callDashboardContentMethod($modelClass, $method, $panel);
            $content = (object) $result;
        } catch (\Throwable $e) {
            \Log::error('Dashboard panel content failed: ' . ($panel->function ?? 'null'), [
                'tab' => $tab,
                'panel' => $panel_name,
                'error' => $e->getMessage(),
            ]);

            return [
                'counter' => (int) $panel->counter,
                'update_tag' => 0,
                'error' => true,
                'message' => trans('messages.Panel could not be loaded'),
                'html' => '<div id="' . e($panel->panel) . '" data-open="1" class="panel-body alert alert-danger">Panel could not be loaded.</div>',
            ];
        }

        $dataCount = count($content->data ?? []);

        if($dataCount != $panel->counter) {
            dashboard::where('tab', $tab)->where('panel', $panel_name)->update(['counter' => (int) ($content->counter ?? $dataCount)]);
        }
        
        $data = [
            'details' => $content,
            'tab' => $tab,
            'panel' => $panel,
            'panelDomId' => 'dashboard_panel_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $panel->tab . '_' . $panel->store . '_' . $panel->panel),
        ];
        
        return [
            'counter' => $dataCount,
            'update_tag' => ($dataCount != $panel->counter) ? 1 : 0,
            'html' => view('areas/dashboard/includes/counters_content')->with($data)->render()
        ];
    }

    public static function externalOrderInvoiceExVat( $tab, $panel ){ 

        $data = [];
        $ids_exceptions = [];
        $exceptions = asm_dashboard::getExceptions('asd_orders_exvat');

        foreach($exceptions AS $exception){
            $ids_exceptions[] = $exception->id_product;
        }    
        
        $bd_data = self::asdOrdersBase()
            ->leftJoin(self::prefix() . 'customer as c', 'c.id_customer', '=', 'o.id_customer')
            ->leftJoin(self::prefix() . 'address as ai', 'ai.id_address', '=', 'o.id_address_invoice')
            ->where('o.date_add', '>', now()->subDays(5))
            ->where(function ($query) {
                $query->where('c.id_default_group', 4)
                    ->orWhere(function ($missingVat) {
                        $missingVat->whereNull('ai.vat_number')
                            ->orWhere('ai.vat_number', '');
                    });
            })
            ->whereNotIn('o.id_order', $ids_exceptions)
            ->select('o.id_order', 'o.reference', 'o.total_products', 'o.total_products_wt')
            ->orderBy('o.id_order', 'DESC')
            ->get();

        foreach($bd_data AS $item){
            $data[] = ['clean' => 'ASD_' . $item->id_order, 'id_order' => $item->id_order, 'reference' => $item->reference, 'total_products' => $item->total_products, 'total_products_wt' => $item->total_products_wt];
        }

        return [
            'name'              => trans('dashboard.ASD - ORDER INVOICE EXVAT'),
            'col'               => 4,
            'item_id'           => 'counter_asd_orders_exvat',
            'prestashop'        => PrestashopAdminLinkService::dashboardOrderLink('id_order', 'ASD'),
            'columns'           => ['clean', 'id_order', 'total_products', 'total_products_wt'],
            'exception_fields'  => ['asd_orders_exvat', 'id_order', 'total_products', 'total_products_wt'],   
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    
    public static function orderInvoiceExVat($tab = null, $panel = null){ 
        if (isset($panel->store) && strtoupper((string) $panel->store) === 'ASD') {
            return self::externalOrderInvoiceExVat($tab ?? 'finance', $panel);
        }

        $data = [];
        $ids_exceptions = [];
        $prefix = self::prefix();

        $exceptions = asm_dashboard::getExceptions('asm_orders_exvat');

        foreach($exceptions AS $exception){
            $ids_exceptions[] = $exception->id_product;
        }

        $bd_data = self::ordersBase('ASM')
            ->leftJoin($prefix . 'customer as c', 'c.id_customer', '=', 'o.id_customer')
            ->leftJoin($prefix . 'address as ai', 'ai.id_address', '=', 'o.id_address_invoice')
            ->where('o.date_add', '>', now()->subDays(5))
            ->where(function ($query) {
                $query->where('c.id_default_group', 4)
                    ->orWhere(function ($missingVat) {
                        $missingVat->where(function ($vat) {
                                $vat->whereNull('ai.vat_number')
                                    ->orWhere('ai.vat_number', '');
                            })
                            ->whereNotNull('ai.company')
                            ->where('ai.company', '<>', '');
                    });
            })
            ->whereNotIn('o.id_order', $ids_exceptions)
            ->select('o.id_order', 'o.reference', 'o.current_state', 'o.total_products', 'o.total_products_wt')
            ->orderBy('o.id_order', 'DESC')
            ->get();
        
        foreach($bd_data AS $item){
            $data[] = ['clean' => $item->id_order, 'id_order' => $item->id_order, 'reference' => $item->reference, 'total_products' => $item->total_products, 'total_products_wt' => $item->total_products_wt ];
        }
        
        return [
            'name'              => trans('dashboard.PRODUCTS WITHOUT DISCOUNT'),
            'col'               => 4,
            'item_id'           => 'counter_asm_orders_exvat',
            'prestashop'        => PrestashopAdminLinkService::dashboardOrderLink('id_order', 'ASM'),
            'columns'           => ['clean', 'id_order', 'reference', 'total_products', 'total_products_wt'],
            'exception_fields'  => ['asm_orders_exvat', 'id_order', 'reference', 'total_products', 'total_products_wt'],   
            'counter'           => count($data),
            'data'              => $data
        ]; 
    }
    

    public static function externalOrdersWithoutPaymentAccepted( $tab, $panel ){ 

        $data = [];
        $ids_exceptions = [];
        $exceptions = asm_dashboard::getExceptions('asd_orders_without_payment');

        foreach($exceptions AS $exception){
            $ids_exceptions[] = $exception->id_product;
        }    
        
        $paidStates = array_map('intval', config('allstars.auto_orders.paid_order_states', [2, 3, 4, 5, 15, 16, 28]));
        $paidStatesWithoutPaymentAccepted = array_values(array_diff($paidStates, [2]));
        $bd_data = self::asdOrdersBase()
            ->whereIn('o.current_state', $paidStatesWithoutPaymentAccepted)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from(self::prefix() . 'order_history as oh')
                    ->whereColumn('oh.id_order', 'o.id_order')
                    ->where('oh.id_order_state', 2);
            })
            ->whereNotIn('o.id_order', $ids_exceptions)
            ->select('o.id_order', 'o.reference')
            ->orderBy('o.id_order', 'DESC')
            ->get();

        foreach($bd_data AS $item){
            $data[] = ['clean' => 'ASD_' . $item->id_order, 'id_order' => $item->id_order, 'reference' => $item->reference, 'other' => ''];
        }

        return [
            'name'              => trans('dashboard.ASD - ORDERS WITHOUT PAYMENT ACCEPTED'),
            'col'               => 4,
            'item_id'           => 'counter_asd_orders_without_payment',
            'prestashop'        => PrestashopAdminLinkService::dashboardOrderLink('id_order', 'ASD'),
            'columns'           => ['clean', 'id_order', 'reference', 'other'],
            'exception_fields'  => ['asd_orders_without_payment', 'id_order', 'reference', 'other'],   
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    
    public static function externalOrdersWithoutShipping( $tab, $panel ){ 

        $data = [];
        $ids_exceptions = [];
        $exceptions = asm_dashboard::getExceptions('asd_orders_without_shipping');

        foreach($exceptions AS $exception){
            $ids_exceptions[] = $exception->id_product;
        }    
        
        $prefix = self::prefix();

        $bd_data = self::asdOrdersBase()
            ->join($prefix . 'customer as c', 'c.id_customer', '=', 'o.id_customer')
            ->join($prefix . 'address as ad', 'ad.id_address', '=', 'o.id_address_delivery')
            ->join($prefix . 'address as ai', 'ai.id_address', '=', 'o.id_address_invoice')
            ->whereIn('o.current_state', array_map('intval', config('allstars.auto_orders.paid_order_states', [2, 3, 4, 5, 15, 16])))
            ->whereExists(function ($query) use ($prefix) {
                $query->select(DB::raw(1))
                    ->from($prefix . 'order_history as oh')
                    ->whereColumn('oh.id_order', 'o.id_order')
                    ->whereColumn('oh.id_order_state', 'o.current_state');
            })
            ->whereNotExists(function ($query) use ($prefix) {
                $query->select(DB::raw(1))
                    ->from($prefix . 'order_detail as od')
                    ->whereColumn('od.id_order', 'o.id_order')
                    ->where('od.product_reference', 'LIKE', 'shipping\_%');
            })
            ->whereNotIn('o.id_order', $ids_exceptions)
            ->select('o.id_order', 'o.reference')
            ->orderBy('o.id_order', 'DESC')
            ->get();

        foreach($bd_data AS $item){
            $data[] = ['clean' => 'ASD_' . $item->id_order, 'id_order' => $item->id_order, 'reference' => $item->reference, 'other' => ''];
        }

        return [
            'name'              => trans('dashboard.ASD - ORDERS WITHOUT PAYMENT ACCEPTED'),
            'col'               => 4,
            'item_id'           => 'counter_asd_orders_without_shipping',
            'prestashop'        => PrestashopAdminLinkService::dashboardOrderLink('id_order', 'ASD'),
            'columns'           => ['clean', 'id_order', 'reference', 'other'],
            'exception_fields'  => ['asd_orders_without_shipping', 'id_order', 'reference', 'other'],   
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
        
    public static function externalOrdersDuplicatedOrder( $tab, $panel ){ 

        $data = [];
        $ids_exceptions = [];
        $exceptions = asm_dashboard::getExceptions('asd_orders_duplicated_orders');

        foreach($exceptions AS $exception){
            $ids_exceptions[] = $exception->id_product;
        }    
        
        $duplicates = self::asdOrdersBase()
            ->select('o.reference', DB::raw('COUNT(*) as repeated'))
            ->groupBy('o.reference')
            ->havingRaw('COUNT(*) > 1');

        $bd_data = self::asdOrdersBase()
            ->joinSub($duplicates, 'dup', 'dup.reference', '=', 'o.reference')
            ->whereNotIn('o.id_order', $ids_exceptions)
            ->select('o.id_order', 'dup.repeated')
            ->orderBy('o.id_order', 'DESC')
            ->get();

        foreach($bd_data AS $item){
            $data[] = ['clean' => 'ASD_' . $item->id_order, 'id_order' => $item->id_order, 'repeated' => $item->repeated, 'other' => ''];
        }

        return [
            'name'              => trans('dashboard.ASD - ORDERS WITHOUT PAYMENT ACCEPTED'),
            'col'               => 4,
            'item_id'           => 'counter_asd_orders_without_shipping',
            'prestashop'        => PrestashopAdminLinkService::dashboardOrderLink('id_order', 'ASD'),
            'columns'           => ['clean', 'id_order', 'repeated', 'other'],
            'exception_fields'  => ['asd_orders_duplicated_orders', 'id_order', 'repeated', 'other'],   
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    
    public static function externalOrdersPartialShipping( $tab, $panel ){ 

        $data = [];
        $bd_data = self::asdOrdersBase()
            ->where('o.current_state', 28)
            ->select('o.id_order', 'o.reference')
            ->orderBy('o.id_order', 'DESC')
            ->get();

        foreach($bd_data AS $item){
            $data[] = ['id_order' => $item->id_order, 'reference' => $item->reference, 'other' => ''];
        }

        return [
            'name'              => trans('dashboard.ASD - ORDERS - Partial Shipping'),
            'col'               => 4,
            'item_id'           => 'counter_asd_orders_partial_shipping',
            'prestashop'        => PrestashopAdminLinkService::dashboardOrderLink('id_order', 'ASD'),
            'columns'           => ['id_order', 'reference', 'other'],
            'exception_fields'  => ['id_order', 'reference', 'other'],   
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    
    public static function externalOrdersWaitingInfo( $tab, $panel ){ 

        $data = [];
        $bd_data = self::asdOrdersByStateName('%waiting%info%');

        foreach($bd_data AS $item){
            $data[] = ['id_order' => $item->id_order, 'reference' => $item->reference, 'other' => ''];
        }

        return [
            'name'              => trans('dashboard.ASD - ORDERS - Waiting Info'),
            'col'               => 4,
            'item_id'           => 'counter_asd_orders_waiting_info',
            'prestashop'        => PrestashopAdminLinkService::dashboardOrderLink('id_order', 'ASD'),
            'columns'           => ['id_order', 'reference', 'other'],
            'exception_fields'  => ['id_order', 'reference', 'other'],   
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    
    public static function externalWarrantyOrders( $tab, $panel ){ 

        $data = [];
        $bd_data = self::asdWarrantyRows();

        foreach($bd_data AS $item){
            $data[] = [
                'id_order' => (int) $item->id_order,
                'reference' => $item->reference,
                'products' => $item->products,
                'url' => (int) $item->id_order > 0
                    ? PrestashopAdminLinkService::dashboardOrderAdminUrl(
                        (int) $item->id_order,
                        config('allstars.auto_orders.shop_codes', [])[(int) $item->id_shop] ?? 'ASM'
                    )
                    : null,
            ];
        }

        return [
            'name'              => trans('dashboard.ASD - WARRANTY ORDERS'),
            'col'               => 4,
            'item_id'           => 'counter_asd_warranty_orders',
            'prestashop'        => PrestashopAdminLinkService::dashboardOrderLink('id_order', 'ASD'),
            'columns'           => ['id_order', 'reference', 'products'],
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    
    public static function externalNoHousingWithStock( $tab, $panel ){ 

        $data = [];
        $bd_data = self::asdNoHousingWithStockRows();

        foreach($bd_data AS $item){
            $data[] = ['clean' => 'ASD_' . $item->id_product, 'id_product' => $item->id_product, 'reference' => $item->reference, 'other' => ''];
        }

        return [
            'name'              => trans('dashboard.ASD - No housing with stock'),
            'col'               => 4,
            'item_id'           => 'counter_asd_no_housing_with_stock',
            'prestashop'        => PrestashopAdminLinkService::dashboardProductLink('id_product', 'ASD'),
            'columns'           => ['id_product', 'reference', 'other'],
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    
    public static function externalOrdersPricesDiff( $tab, $panel ){ 

        $data = [];
        $ids_exceptions = [];
        $exceptions = asm_dashboard::getExceptions('asd_orders_price_diff');
        
        foreach($exceptions AS $exception){
            $ids_exceptions[] = $exception->id_product;
        }    
        
        $bd_data = self::asdOrdersPriceDiffRows($ids_exceptions);

        foreach($bd_data AS $item){
            $data[] = ['clean' => 'ASD_' . $item->id_order, 'id_order' => $item->id_order, 'reference' => $item->reference, 'total_products' => $item->total_products, 'total_products_wt' => $item->total_products_wt, 'soma_excl' => $item->soma_excl, 'soma_incl' => $item->soma_incl, 'other' => '' ];
        }

        return [
            'name'              => trans('dashboard.ASD - ORDERS PRICE DIFF'),
            'col'               => 6,
            'item_id'           => 'counter_asd_orders_price_diff',
            'prestashop'        => PrestashopAdminLinkService::dashboardOrderLink('id_order', 'ASD'),
            'columns'           => ['clean', 'id_order', 'reference', 'total_products', 'total_products_wt', 'soma_excl', 'soma_incl'],
            'exception_fields'  => ['asd_orders_price_diff', 'id_order', 'reference', 'other'],   
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    































    
    private static function prefix(): string
    {
        return env('DB2_DB_prefix', env('DB2_prefix', 'ps_'));
    }

    private static function shopId(string $store): int
    {
        $store = strtoupper($store);

        return (int) (
            config("allstars.stores.{$store}.id_shop")
            ?: config("shops.{$store}.id")
            ?: match ($store) {
                'ASD' => 3,
                'ASM' => 2,
                default => 0,
            }
        );
    }

    private static function ordersBase(string $store)
    {
        return DB::connection('mysql2')
            ->table(self::prefix() . 'orders as o')
            ->where('o.id_shop', self::shopId($store));
    }

    private static function asdOrdersBase()
    {
        return self::ordersBase('ASD');
    }

    private static function asdOrdersByStateName(string $pattern)
    {
        $prefix = self::prefix();

        return self::asdOrdersBase()
            ->join($prefix . 'order_state_lang as osl', function ($join) {
                $join->on('osl.id_order_state', '=', 'o.current_state')
                    ->where('osl.id_lang', 1);
            })
            ->where('osl.name', 'LIKE', $pattern)
            ->select('o.id_order', 'o.reference')
            ->orderBy('o.id_order', 'DESC')
            ->get();
    }

    private static function asdWarrantyRows()
    {
        $prefix = self::prefix();

        return DB::connection('mysql2')
            ->table($prefix . 'orders as o')
            ->join($prefix . 'order_state_lang as osl', function ($join) {
                $join->on('osl.id_order_state', '=', 'o.current_state')
                    ->where('osl.id_lang', 2);
            })
            ->join($prefix . 'order_detail as od', 'od.id_order', '=', 'o.id_order')
            ->leftJoin($prefix . 'product as p', 'p.id_product', '=', 'od.product_id')
            ->leftJoin($prefix . 'manufacturer as m', 'm.id_manufacturer', '=', 'p.id_manufacturer')
            ->where('osl.name', 'LIKE', '%warranty%')
            ->select([
                'o.id_order',
                'o.id_shop',
                'o.reference',
                DB::raw('GROUP_CONCAT(DISTINCT od.product_reference ORDER BY od.product_reference SEPARATOR ", ") as products'),
            ])
            ->groupBy('o.id_order', 'o.id_shop', 'o.reference')
            ->orderByDesc('o.id_order')
            ->get();
    }

    private static function asdNoHousingWithStockRows()
    {
        $prefix = self::prefix();
        $shopId = self::shopId('ASD');

        return DB::connection('mysql2')
            ->table($prefix . 'product as p')
            ->join($prefix . 'product_shop as ps', function ($join) use ($shopId) {
                $join->on('ps.id_product', '=', 'p.id_product')
                    ->where('ps.id_shop', $shopId);
            })
            ->join($prefix . 'stock_available as sa', function ($join) use ($shopId) {
                $join->on('sa.id_product', '=', 'p.id_product')
                    ->where('sa.id_shop', $shopId);
            })
            ->where('ps.active', 1)
            ->where('sa.quantity', '>', 0)
            ->where(function ($query) {
                $query->whereNull('p.location')
                    ->orWhere('p.location', '');
            })
            ->select('p.id_product', 'p.reference')
            ->groupBy('p.id_product', 'p.reference')
            ->orderBy('p.id_product')
            ->get();
    }

    private static function asdOrdersPriceDiffRows(array $exceptions)
    {
        $prefix = self::prefix();

        return self::asdOrdersBase()
            ->join($prefix . 'order_detail as od', 'od.id_order', '=', 'o.id_order')
            ->whereNotIn('o.id_order', $exceptions)
            ->select([
                'o.id_order',
                'o.reference',
                'o.total_products',
                'o.total_products_wt',
                DB::raw('ROUND(SUM(od.total_price_tax_excl), 2) as soma_excl'),
                DB::raw('ROUND(SUM(od.total_price_tax_incl), 2) as soma_incl'),
            ])
            ->groupBy('o.id_order', 'o.reference', 'o.total_products', 'o.total_products_wt')
            ->havingRaw('ABS(ROUND(SUM(od.total_price_tax_excl), 2) - ROUND(o.total_products, 2)) > 0.01 OR ABS(ROUND(SUM(od.total_price_tax_incl), 2) - ROUND(o.total_products_wt, 2)) > 0.01')
            ->orderBy('o.id_order', 'DESC')
            ->get();
    }

    public static function productsWithoutDiscounts($tab, $panel)
    {
        $prefix = env('DB2_DB_prefix', env('DB2_prefix', 'ps_'));
        $shopId = self::shopId('ASD');
        $now = now()->format('Y-m-d H:i:s');
    
        $exceptions = asm_dashboard::getExceptions('asd_products_without_discounts')
            ->pluck('id_product')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    
        $rows = DB::connection('mysql2')
            ->table($prefix . 'product as p')
            ->join($prefix . 'product_shop as ps', function ($join) use ($shopId) {
                $join->on('ps.id_product', '=', 'p.id_product')
                    ->where('ps.id_shop', '=', $shopId);
            })
            ->leftJoin($prefix . 'specific_price as sp', function ($join) use ($shopId, $now) {
                $join->on('sp.id_product', '=', 'p.id_product')
                    ->where(function ($query) use ($shopId) {
                        $query->where('sp.id_shop', '=', 0)
                            ->orWhere('sp.id_shop', '=', $shopId);
                    })
                    ->where(function ($query) {
                        $query->where('sp.reduction', '>', 0)
                            ->orWhere('sp.price', '>=', 0);
                    })
                    ->where(function ($query) use ($now) {
                        $query->whereNull('sp.from')
                            ->orWhere('sp.from', '0000-00-00 00:00:00')
                            ->orWhere('sp.from', '<=', $now);
                    })
                    ->where(function ($query) use ($now) {
                        $query->whereNull('sp.to')
                            ->orWhere('sp.to', '0000-00-00 00:00:00')
                            ->orWhere('sp.to', '>=', $now);
                    });
            })
            ->whereNull('sp.id_specific_price')
            ->where('ps.active', 1)
            ->when(!empty($exceptions), fn ($query) => $query->whereNotIn('p.id_product', $exceptions))
            ->select(['p.id_product', 'p.reference'])
            ->orderBy('p.id_product', 'ASC')
            ->get();
    
        return self::dashboardPanel(
            trans('dashboard.ASD - PRODUCTS WITHOUT DISCOUNT'),
            'counter',
            'asd_products_without_discount',
            ['clean', 'id_product', 'reference'],
            $rows->map(fn ($item) => [
                'clean' => 'ASD_' . $item->id_product,
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'extra' => 0,
                'url' => \App\Services\Prestashop\PrestashopAdminLinkService::dashboardProductAdminUrl((int) $item->id_product, 'ASD'),
            ]),
            ['exception_fields' => ['asd_products_without_discounts', 'id_product', 'reference', 'extra']],
            \App\Services\Prestashop\PrestashopAdminLinkService::dashboardProductLink('id_product', 'ASD')
        );
    }
    
    public static function ordersReferenceWithSpaces($tab, $panel)
    {
        $prefix = env('DB2_DB_prefix', env('DB2_prefix', 'ps_'));
        $shopId = self::shopId('ASD');
    
        $exceptions = asm_dashboard::getExceptions('asd_product_reference_with_spaces')
            ->pluck('id_product')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    
        $rows = DB::connection('mysql2')
            ->table($prefix . 'product as p')
            ->join($prefix . 'product_shop as ps', function ($join) use ($shopId) {
                $join->on('ps.id_product', '=', 'p.id_product')
                    ->where('ps.id_shop', '=', $shopId);
            })
            ->whereNotNull('p.reference')
            ->where('p.reference', '<>', '')
            ->where('p.reference', 'REGEXP', '[[:space:]]')
            ->when(!empty($exceptions), fn ($query) => $query->whereNotIn('p.id_product', $exceptions))
            ->select(['p.id_product', 'p.reference'])
            ->orderBy('p.id_product', 'ASC')
            ->get();
    
        return self::dashboardPanel(
            trans('dashboard.ASD - PRODUCT REFERENCES WITH SPACES'),
            'counter',
            'asd_product_reference_with_spaces',
            ['clean', 'id_product', 'reference'],
            $rows->map(fn ($item) => [
                'clean' => 'ASD_' . $item->id_product,
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'extra' => 0,
                'url' => \App\Services\Prestashop\PrestashopAdminLinkService::dashboardProductAdminUrl((int) $item->id_product, 'ASD'),
            ]),
            ['exception_fields' => ['asd_product_reference_with_spaces', 'id_product', 'reference', 'extra']],
            \App\Services\Prestashop\PrestashopAdminLinkService::dashboardProductLink('id_product', 'ASD')
        );
    }
    
    public static function productsNoImage($tab, $panel)
    {
        $rows = AsdImage::missingRows();
    
        return self::dashboardPanel(
            trans('dashboard.ASD - No images'),
            'counter',
            'asd_product_no_image',
            ['id_product', 'reference', 'manufacturer'],
            $rows->map(fn ($item) => [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'manufacturer' => $item->manufacturer,
                'extra' => 0,
                'url' => \App\Services\Prestashop\PrestashopAdminLinkService::dashboardProductAdminUrl((int) $item->id_product, 'ASD'),
            ]),
            [],
            \App\Services\Prestashop\PrestashopAdminLinkService::dashboardProductLink('id_product', 'ASD')
        );
    }
    
    public static function productsPriceIssue($tab, $panel)
    {
        $prefix = env('DB2_DB_prefix', env('DB2_prefix', 'ps_'));
        $shopId = self::shopId('ASD');
    
        $exceptions = asm_dashboard::getExceptions('asd_product_price_issues')
            ->pluck('id_product')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    
        $rows = DB::connection('mysql2')
            ->table($prefix . 'product_shop as ps')
            ->join($prefix . 'product as p', 'p.id_product', '=', 'ps.id_product')
            ->where('ps.id_shop', $shopId)
            ->whereColumn('ps.wholesale_price', '>', 'ps.price')
            ->when(!empty($exceptions), fn ($query) => $query->whereNotIn('ps.id_product', $exceptions))
            ->select(['ps.id_product', 'p.reference', 'ps.wholesale_price', 'ps.price'])
            ->orderBy('ps.id_product', 'ASC')
            ->get();
    
        return self::dashboardPanel(
            trans('dashboard.ASD - Wholesale > price ( ex VAT)'),
            'counter',
            'asd_product_price_issues',
            ['clean', 'id_product', 'reference', 'wholesale_price', 'price'],
            $rows->map(fn ($item) => [
                'clean' => 'ASD_' . $item->id_product,
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'wholesale_price' => $item->wholesale_price,
                'price' => $item->price,
                'extra' => 0,
                'url' => \App\Services\Prestashop\PrestashopAdminLinkService::dashboardProductAdminUrl((int) $item->id_product, 'ASD'),
            ]),
            ['exception_fields' => ['asd_product_price_issues', 'id_product', 'reference', 'extra']],
            \App\Services\Prestashop\PrestashopAdminLinkService::dashboardProductLink('id_product', 'ASD')
        );
    }

    
}
