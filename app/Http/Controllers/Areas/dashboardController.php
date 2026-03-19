<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\CustomTools\mailsController;

use App\Models\prestashop\asm_dashboard;

use App\Models\prestashop\product;
use App\Models\prestashop\product_lang;
use App\Models\prestashop\product_comment;
use App\Models\prestashop\manufacturers;
use App\Models\prestashop\orders;
use App\Models\prestashop\order_carrier;
use App\Models\prestashop\orders_details;
use App\Models\prestashop\order_payment;
use App\Models\prestashop\stock_available;
use App\Models\prestashop\issues;
use App\Models\prestashop\groupinc_configuration;
use App\Models\prestashop\specific_price;
use App\Models\prestashop\asm_email_alert;

use App\Models\modules\dashboard\dashboard;


class dashboardController extends Controller
{
    public $actions;
    public $breadcrumbs;
    //private static $expectedEvolution = 1.19659;
    //private static $expectedEvolution = 1.19607;
    private static $expectedEvolution = 1.15;
    //private static $objective = 7474668.00;
    private static $objective = 9076478.00;


    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){

        $this->breadcrumbs[] = [ 'name' =>  trans('Dashboard'), 'url' => route('dashboard.index')];

        $data = [
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs,
            'counters'      => [],
            'panels'        => [],
            'manufacturers' => manufacturers::getManufacturersForSelect()
        ];

        return View::make('areas/dashboard/index')->with($data);
    }

    public function post(Request $request){

        $sold = array();

        if($request->action == 'getSalesHistory'){
    
            $details = orders::select( '*', 'ps_stock_available.quantity AS stock', 'ps_product.reference AS reference', 'ps_product_attribute.reference AS attr_reference', DB::raw('SUM(ps_order_detail.product_quantity) as sold'))
            ->leftJoin('ps_order_detail', 'ps_order_detail.id_order', '=', 'ps_orders.id_order')
            ->leftJoin('ps_product', 'ps_product.id_product', '=', 'ps_order_detail.product_id')
            ->leftJoin('ps_product_attribute', 'ps_product_attribute.id_product_attribute', '=', 'ps_order_detail.product_attribute_id')
            ->leftJoin('ps_stock_available', function($join){
                $join->on('ps_order_detail.product_id', '=', 'ps_stock_available.id_product');
                $join->on('ps_order_detail.product_attribute_id', '=', 'ps_stock_available.id_product_attribute');
            })
            ->where('ps_orders.date_add', '>', $request->date)
            ->whereIN('ps_orders.current_state', [2, 3, 4, 5, 15, 16, 28])
            ->where('id_manufacturer', $request->brand)
            ->groupBy('product_reference')
            ->get();

            foreach($details AS $product){
                
                $key = (strlen($product->attr_reference) > 0) ? $product->attr_reference : $product->reference;
                $sold[$key] = [ 
                    'origin' => 'ASM',
                    'reference' => $product->reference,
                    'reference_attr' => $product->attr_reference,
                    'stock' => $product->stock,
                    'sold' => $product->sold,
                ];

            }
            
            $manufacturer = manufacturers::where('id_manufacturer', $request->brand)->first();

            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', 'all-stars-distribution.com/custom/api/dashboards/sold.php?brand=' . $manufacturer->name . '&date=' . $request->date);
            
            $data = array();            
            if($response->getStatusCode() == 200) $data = json_decode($response->getBody(), true);
            
            /** TO DELETE ON MULTISTORE **/

            $global = array();
            foreach($sold AS $key => $item){
                
                if(isset($data['data'][$key])){
                    $sold_asd = $item['sold'] + $data['data'][$key]['sold'];  
                    $origin = 'CROSS';
                    unset($data['data'][$key]);
                    
                }else{ 
                    $sold_asd = $item['sold'];
                    $origin = 'ASM';
                }

                $global[] = [
                    'origin' => $origin,
                    'reference' => $item['reference'], 
                    'reference_attr' => $item['reference_attr'], 
                    'stock' => $item['stock'], 
                    'sold' => $sold_asd
                ];
                
            }
            
            $sold = array_merge($global, $data['data']);
        
            $columns = [ 'ORIGIN', 'REFERENCE', 'ATTRIBUTE', 'STOCK', 'SOLD' ];

            $file = fopen(public_path() . '/admin/download/sold.csv', 'w');
            fputcsv($file, $columns, ';');
            foreach ($sold as $item){
                fputcsv($file, [$item['origin'], $item['reference'], $item['reference_attr'], $item['stock'], $item['sold']], ';');
            }
    
            fclose($file);
            
            $url = '/admin/download/sold.csv';
            
            $viewRendered = view('areas/dashboard/includes/sold', compact('sold', 'url'))->render();

            return response()->json([ 'html' => $viewRendered ]);
        }elseif( $request->action == 'requestedProductSendEmail' ){
            
            $requested_product = asm_email_alert::find($request->id);
            
            $product_detail = product::with('lang')->where('id_product' , $requested_product->id_product)->first();
            
            $data = [
                'requested' => $requested_product,
                'product_info' => $product_detail
            ];
            
            /** Faz o envio do email de notificação **/
            $email = NEW mailsController();
            $html = $email->createStructure('ASM', 'requestedProducts', trans('mails.Requested products notification'), $data, $requested_product->id_lang);
            $email->send($requested_product->email, $html, trans('mails.Requested products notification'));
            
            /** Remove o registo de pedido de notificação do roduto da base de dados **/
            asm_email_alert::where('id', $request->id)->delete();
            
            return response()->json([ 'result' => 'success' ]);
            
        }elseif( $request->action == 'removeItem' ){
            
            asm_email_alert::where('id', $request->id)->delete();
            
            return response()->json([ 'result' => 'success' ]);

        }elseif( $request->action == 'addException' ){
            
            asm_dashboard::addException($request);
            
            return response()->json([ 'result' => 'success' ]);
            
        }
    }


    public function daily_stats(){

        $byMonth    = order_payment::objectiveByMonth();
        $byMonthASD = order_payment::objectiveByMonthASD();
        
        $asdActual  = order_payment::getASDActual();
        $asdHomologo= order_payment::getASDHomologo();
    
        $months = [
            0  => self::getMonthlyValue( $byMonth[1], 'January',    $byMonthASD[1]  ),
            1  => self::getMonthlyValue( $byMonth[2], 'February',   $byMonthASD[2]  ),
            2  => self::getMonthlyValue( $byMonth[3], 'March',      $byMonthASD[3]  ),
            3  => self::getMonthlyValue( $byMonth[4], 'April',      $byMonthASD[4]  ),
            4  => self::getMonthlyValue( $byMonth[5], 'May',        $byMonthASD[5]  ),
            5  => self::getMonthlyValue( $byMonth[6], 'June',       $byMonthASD[6]  ),
            6  => self::getMonthlyValue( $byMonth[7], 'July',       $byMonthASD[7]  ),
            7  => self::getMonthlyValue( $byMonth[8], 'August',     $byMonthASD[8]  ),
            8  => self::getMonthlyValue( $byMonth[9], 'September',  $byMonthASD[9]  ),
            9  => self::getMonthlyValue( $byMonth[10], 'October',   $byMonthASD[10] ),
            10 => self::getMonthlyValue( $byMonth[11], 'November',  $byMonthASD[11] ),
            11 => self::getMonthlyValue( $byMonth[12], 'December',  $byMonthASD[12] ),
            12 => self::getMonthlyValueTotal($byMonth, 'Total',$byMonthASD)
        ];
        $counters = order_payment::getCounters();

        $goals = order_payment::projection(self::$expectedEvolution);
        

        if( is_null($asdHomologo) ){
            $value_objectivo = 0;
        }else{
            $value_objectivo = $asdHomologo->value_objectivo;
        }
        
        $asdValue = (!isset( $asdActual->value)) ? 0 : $asdActual->value;
        
        /** calculation to show counter **/
        $until_today = number_format( ( $counters->getActual + $asdValue ), 2, ',', ' ') . ' €';
        $accumulated_last_year_until_now = number_format( ( $counters->getAcumuladoAnoAnteriorHomologo  + $value_objectivo ), 2, ',', ' ') . ' €';
        $difference = number_format( ( ( $counters->getActual + $asdValue ) - ( $counters->getAcumuladoAnoAnteriorHomologo  + $value_objectivo ) ), 2, ',', ' ') . ' €';
        
        $objective_until_today = number_format( ( $counters->getAcumuladoAnoAnteriorHomologo  + $value_objectivo ) * self::$expectedEvolution, 2, ',', ' ') . ' €';
        $difference_objective_until_today = number_format( ( ( ( $counters->getActual + $asdValue ) - ( $counters->getAcumuladoAnoAnteriorHomologo  + $value_objectivo ) * self::$expectedEvolution ) ), 2, ',', ' ') . ' €';
        $missing_to_objective = number_format( ( ( $counters->getActual + $asdValue ) - self::$objective ), 2, ',', ' ') . ' €';
        /** calculation to show counter **/

        $side = (object)[
            'until_today' => $until_today,
            'accumulated_last_year_until_now' => $accumulated_last_year_until_now,
            'difference' => $difference,
            'objective_until_today' => $objective_until_today,
            'difference_objective_until_today' => $difference_objective_until_today,
            'objective' => number_format(self::$objective, 2, ',', ' ') . ' €',
            'missing_to_objective' => $missing_to_objective
        ];

        return response()->json([ 'html' => view('areas/dashboard/includes/daily_stats', compact('months', 'side', 'goals'))->render() ]);
    }
    
    private function getMonthlyValue($data, $name, $data_extra = null){
        
        $current    = ( isset($data_extra->current) ) ? ( $data->current + $data_extra->current ) : $data->current;
        $lastYear   = ( isset($data_extra->lastYear) ) ? ( $data->lastYear + $data_extra->lastYear ) : $data->lastYear;
        $objective  = ( $lastYear * self::$expectedEvolution);
        $difference = $current - ( $lastYear * self::$expectedEvolution);
        
        return (object)[
            'name'          => $name,
            'accomplished'  => number_format( $current,    2, ',', ' ') . ' €',
            'last_year'     => number_format( $lastYear,   2, ',', ' ') . ' €',
            'objective_value'     => $objective,
            'objective'     => number_format( $objective,  2, ',', ' ') . ' €',
            'difference'    => number_format( $difference, 2, ',', ' ') . ' €',
            'asm'           => number_format( $data->lastYear, 2, ',', ' ') . ' €',
            'asd'           => number_format( $data_extra->lastYear, 2, ',', ' ') . ' €',
        ];
    }
    
    private function getMonthlyValueTotal($data, $name, $data_extra = null){
        
        $total_current = 0;
        $total_lastYear = 0;
        
        foreach($data AS $key => $month){
            $total_current  += ($data[$key]->current  + $data_extra[$key]->current);
            $total_lastYear += ($data[$key]->lastYear + $data_extra[$key]->lastYear);
        }
        
        $objective = $total_lastYear * self::$expectedEvolution;

        
        return (object)[
            'name'          => $name,
            'accomplished'  => number_format( $total_current,  2, ',', ' ') . ' €',
            'last_year'     => number_format( $total_lastYear, 2, ',', ' ') . ' €',
            'objective'     => number_format( $objective,      2, ',', ' ') . ' €',
            'difference'    => ''
        ];
        
    }
    
    public function shipping_report(){

        $carrier_data = array();

        $carrier_data['DPD'] = 0;
        $carrier_data['UPS'] = 0;
        $carrier_data['TNT'] = 0;
        $carrier_data['NACEX'] = 0;
        $carrier_data['FEDEX'] = 0;
        $carrier_data['GLS'] = 0;
        
        $totalPaidByCustomer = orders::shippingPaidByCustomer(date('Y'));
        
        $totalByOrder = order_carrier::shippingByOrder(date('Y'));

        return response()->json([ 'html' => view('areas/dashboard/includes/shipping_report', compact('totalPaidByCustomer', 'totalByOrder'))->render() ]);
    }
    
    public function kpi(){

        $asd = orders::getCounters(1, self::$expectedEvolution);
        $asm = orders::getCounters(2, self::$expectedEvolution);
        $er  = orders::getCounters(3, self::$expectedEvolution);
        $em  = orders::getCounters(4, self::$expectedEvolution);
        
        $yesterday_forcast  = ( $asm->yesterday_forcast  + $asd->yesterday_forcast  + $er->yesterday_forcast  + $em->yesterday_forcast  );
        $yesterday_realized = ( $asm->yesterday_realized + $asd->yesterday_realized + $er->yesterday_realized + $em->yesterday_realized );
        
        $today_forcast      = ( $asm->today_forcast  + $asd->today_forcast  + $er->today_forcast  + $em->today_forcast  );
        $today_realized     = ( $asm->today_realized + $asd->today_realized + $er->today_realized + $em->today_realized );
        
        $currency_rates = issues::getCurrencyRates();

        $counters    = order_payment::getCounters();
        $asdActual   = order_payment::getASDActual();
        $asdHomologo = order_payment::getASDHomologo();
        
        $asd_data_atual = 0;
        $asd_data_obj_month = 0;
        
        if(!is_null($asdActual)){
            
            $asd_data_atual = $asdActual->value_month+0;
            $asd_data_obj_month = ( isset($asdActual->value_month)) ? $asdActual->value_month : 0;
        
        }
        
        $byMonth    = order_payment::objectiveByMonth();
        $byMonthASD = order_payment::objectiveByMonthASD();
        

        $monthGoalString = self::getMonthlyValue( $byMonth[date('n')], '',    $byMonthASD[date('n')]  );
        $clean = str_replace(['€', ' '], '', $monthGoalString->objective);
        $clean = str_replace(',', '.', $clean);
        $monthGoal = (float) $clean;
        
        $daysInMonth = date('t');
        $today_forcast = $monthGoalString->objective_value / $daysInMonth;


        if( is_null($asdHomologo) ){
            $value_objectivo_month = 0;
        }else{
            $value_objectivo_month = $asdHomologo->value_objectivo_month;
        }

        $realised_until_today = $counters->getActualCurrentMonth + $asd_data_atual;
        $acumulado_ano_passado = $counters->getASMAcumuladoAnoAnteriorHomologoCurrentMonth + $value_objectivo_month;

        $tier_1 = $acumulado_ano_passado*(self::$expectedEvolution-0.05);
        //$tier_2 = $acumulado_ano_passado*self::$expectedEvolution; 
        
        $tier_2 = $today_forcast * date('j');
        $status = ($realised_until_today / $tier_2) * 100;
        $status_color = 'red';
        
        if( $status > 99.99) $status_color = 'darkgreen';
        if( $status > 104.99) $status_color = 'dodgerblue';

        $goal_until_today_new   = ( ( $realised_until_today - $tier_2 ) / $tier_2 ) * 100;
        $goal_until_today       = ( ( $realised_until_today - $tier_2 ) / $tier_2 ) * 100;
        
        $data = [
            'awaiting' =>   ( $asm->awaiting    + $asd->awaiting    + $er->awaiting     + $em->awaiting     ),
            'packing' =>    ( $asm->packing     + $asd->packing     + $er->packing      + $em->packing      ),
            'shipped' =>    ( $asm->shipped     + $asd->shipped     + $er->shipped      + $em->shipped      ),
            'warranty' =>   ( $asm->warranty    + $asd->warranty    + $er->warranty     + $em->warranty     ),
            'backorders' => ( $asm->backorders  + $asd->backorders  + $er->backorders   + $em->backorders   ),
            'partial' =>    ( $asm->partial     + $asd->partial     + $er->partial      + $em->partial      ),
            'pending' =>    ( $asm->pending     + $asd->pending     + $er->pending      + $em->pending      ),
            'group_result' => number_format($today_realized, 2, ',', ' ') . ' €',

            'realised_until_today' => $realised_until_today,
            'objective_until_today' => $tier_2,
            'acumulado_ano_passado' => $acumulado_ano_passado,
            'status_color' => $status_color,
            'status' => $status,
            
            'daillyGoal' => $today_forcast,
            
            'monthGoal' => $monthGoal,
            'monthGoalValue' => $monthGoalString->objective,
            'monthDays' => $daysInMonth,
            'progress' => ( $realised_until_today / $monthGoal ) * 100,

            'today' => (object)[
                'forcast'  => number_format($today_forcast, 2, ',', ' ') . ' €',   
                'realized' => number_format($today_realized, 2, ',', ' ') . ' €',
                'realized_value' => $today_realized,
                'reached'  => ( $today_realized > $today_forcast) ? 1 : 0,
                'goal_until_today' => $goal_until_today,
                'goal_until_today_new' => $goal_until_today_new,
                'percent'  => ($realised_until_today / $tier_2),
            ],
            
            'yesterday' => (object)[
                'forcast' => number_format($yesterday_forcast, 2, ',', ' ') . ' €',   
                'realized' => number_format($yesterday_realized, 2, ',', ' ') . ' €',
                'realized_value' => $yesterday_realized,
                'reached' => ( $yesterday_realized > $yesterday_forcast) ? 1 : 0
            ],
            
            'realized_asm' => number_format( $asm->today_realized, 2, ',', ' ') . ' €',
            'realized_asd' => number_format( $asd->today_realized, 2, ',', ' ') . ' €',
            'realized_er'  => number_format( $er->today_realized,  2, ',', ' ') . ' €',
            'realized_em'  => number_format( $em->today_realized,  2, ',', ' ') . ' €',
            
            'forcast_asm' => number_format( $asm->today_forcast, 2, ',', ' ') . ' €',
            'forcast_asd' => number_format( $asd->today_forcast, 2, ',', ' ') . ' €',
            'forcast_er'  => number_format( $er->today_forcast,  2, ',', ' ') . ' €',
            'forcast_em'  => number_format( $em->today_forcast,  2, ',', ' ') . ' €',
            
            'reached_asm' => ( $asm->today_realized > $asm->today_forcast) ? 1 : 0,
            'reached_asd' => ( $asd->today_realized > $asd->today_forcast) ? 1 : 0,
            'reached_er'  => ( $er->today_realized  > $er->today_forcast ) ? 1 : 0,
            'reached_em'  => ( $em->today_realized  > $em->today_forcast ) ? 1 : 0,
            
            'yuan'   => number_format( $currency_rates->field_1, 4, ',', ' '),
            'pound'  => number_format( $currency_rates->field_2, 4, ',', ' '),
            'dollar' => number_format( $currency_rates->field_3, 4, ',', ' '),
            'yen'    => number_format( $currency_rates->field_4, 4, ',', ' ')
        ];
        
        return View::make('areas/dashboard/dashboard')->with($data);
    }  
    
}
