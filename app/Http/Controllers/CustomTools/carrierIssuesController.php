<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

use App\Http\Controllers\Controller;

use App\Models\modules\carrierIssues\carrierIssues;
use App\Models\prestashop\country;
use App\Models\prestashop\order_carrier;

class carrierIssuesController extends Controller
{
    public $breadcrumbs = [];
    
    private $carriers = [
            [
                'name' => 'dpd',
                'logo' => '/uploads/logos/carriers/dpd.png',
            ],
            [
                'name' => 'ups',
                'logo' => '/uploads/logos/carriers/ups.png',
            ],
            [
                'name' => 'nacex',
                'logo' => '/uploads/logos/carriers/nacex.png',
            ]
        ];
        
    public function index() {
        $this->breadcrumbs[] = ['name' => 'Logistics', 'url' => route('logistics.index')];
        $this->breadcrumbs[] = ['name' => 'Carrier issues', 'url' => route('carrierIssues.index'), 'no_translation' => 1];

        carrierIssues::updateIssuesDelayDate();

        $carrierIssuesArchived = carrierIssues::getCarrierIssues(0);
        $carrierIssuesActive = carrierIssues::getCarrierIssues(1);
        $carrierIssuesRetention = carrierIssues::getCarrierIssues(2);
        
        $data = [
            'htmlAfterUpload' => '',
            'carrierIssuesArchived' => $carrierIssuesArchived,
            'carrierIssuesActive' => $carrierIssuesActive,
            'carrierIssuesRetention' => $carrierIssuesRetention,
            'countries'     => country::with('lang_en')->get(),
            'breadcrumbs'   => $this->breadcrumbs
        ];

        return View::make('customTools/carrierIssues/index')->with($data);

    }
    
    public function store(Request $request) {
        carrierIssues::saveData($request->all());
        return redirect()->route('carrierIssues.index');
    }
    
    public function destroy(Request $request) {
        return carrierIssues::destroyRow($request->id_issue);
    }

    public function archive(Request $request) {
        return carrierIssues::archiveRow($request->id_issue, $request->status);
    }

    public function edit(Request $request) {
        
        $data = [
            'htmlAfterUpload' => '',
            'issue'     => carrierIssues::getIssue($request->id_issue),
            'countries' => country::with('lang_en')->get()
        ];
        
        $viewRendered = view('customTools.carrierIssues.includes.edit', compact('data'))->render();
        return response()->json([ 'html' => $viewRendered ]);
        
    }

    public function update(Request $request) {
        carrierIssues::updateData($request->all());
        return redirect()->route('carrierIssues.index');
    }

    public function verificationUpload(Request $request) {
        $carrier = $request->carrier;
        return View::make('customTools/carrierIssues/verification/upload', compact('carrier'))->render();
    }

    public function carrierVerifyDPD($carrier) {

        $total = 0;
        $oversized_weight = [];
        $oversized_size = [];
        
        $asm =[];
        $asd =[];
        
        $file = base_path('public/uploads/carriers/' . $carrier . '/' . $carrier . '.csv');
        
        $rows = array();
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                
                if( (int)$data[15] > 0.3){
                    
                    if( $data[16] == 'ALL STARS MOTORSPORT'){
                        $asm[] = $data;
                    }else{
                        $asd[] = $data;
                    }
                    
                    if( (int)$data[9] > 31.49){
                        $oversized_weight[] = $data;
                    }
                    
                    $rows[] = $data;

                    if( $data[15] != 'Total') $total += (int)$data[15];
                }

            }
            fclose($handle);
        }
        
        $asm = self::requestDataFromShop($rows, '', 2, 'ASM');
        
        $asd = json_decode(json_encode(self::requestDataFromASD($rows)), true);

        return View::make('customTools/carrierIssues/verification/verify/' . $carrier, compact('carrier', 'asm', 'asd', 'total', 'oversized_weight', 'oversized_size'))->render();
    }
    

    public function carrierVerifyNacex($carrier) {

        $total = 0;
        $oversized_weight = [];
        $oversized_size = [];
        
        $asm =[];
        $asd =[];
        
        $file = base_path('public/uploads/carriers/' . $carrier . '/' . $carrier . '.csv');
        
        $rows = array();
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                
                $rows[] = $data;
                if( $data[22] != 'Importe(Euros)'){
                    $total += (int)$data[22];
                }
            }
            fclose($handle);
        }

        $asm = self::requestDataFromShop($rows, 'NACEX', 2, 'ASM');
        
        $asd = json_decode(json_encode(self::requestDataFromASD($rows, 'NACEX')), true);

        return View::make('customTools/carrierIssues/verification/verify/nacex', compact('carrier', 'asm', 'asd', 'total', 'oversized_weight', 'oversized_size'))->render();
    }
    

    public function carrierVerifyUPS($carrier) {

        $total = 0;
        $oversized_weight = [];
        $oversized_size = [];
        
        $asm =[];
        $asd =[];
        
        $file = base_path('public/uploads/carriers/' . $carrier . '/' . $carrier . '.csv');
        
        $rows = array();
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                
                if( $data[0] != 'Account Number'){
                    
                    if( strlen($data[4]) > 0 ){
                        $rows[] = $data;
                        $total += (int)$data[23];
                        
                    }
                }
            }
            fclose($handle);
        }

        $asm = self::requestDataFromShop($rows, 'UPS', 2, 'ASM');

        $asd = json_decode(json_encode(self::requestDataFromASD($rows, 'UPS')), true);

        return View::make('customTools/carrierIssues/verification/verify/ups', compact('carrier', 'asm', 'asd', 'total', 'oversized_weight', 'oversized_size'))->render();
        
    }
    
    public function carrierVerify(Request $request) {

        $carrier = $request->carrier;
        if($carrier == 'dpd')   return self::carrierVerifyDPD($carrier);
        if($carrier == 'nacex') return self::carrierVerifyNacex($carrier);
        if($carrier == 'ups')  return self::carrierVerifyUPS($carrier);
        
        return back();
    }

    public function verificationIndex() {

        $data = [
            'htmlAfterUpload' => '',
            'carriers' => $this->carriers
        ];
        
        return View::make('customTools/carrierIssues/verification/index')->with($data);

    }


    private function requestDataFromShop($data, $by_id_order = '', int $shopId = 2, string $store = 'ASM') {
        
        $shop_rows = array();
        $count = 0;
        $prefix = env('DB2_DB_prefix');
            
        foreach($data AS $row){
        
            $count++;
        
            if($by_id_order == ''){
                
                $shippment = DB::connection('mysql2')->table($prefix . 'orders')
                    ->join(       $prefix . 'order_carrier',     $prefix . 'orders.id_order', '=', $prefix . 'order_carrier.id_order')
                    ->leftJoin(   $prefix . 'custom_order_carrier', $prefix . 'order_carrier.id_order_carrier', '=', $prefix . 'custom_order_carrier.id_order_carrier')
                    ->select(
                        $prefix . 'orders.id_order',
                        $prefix . 'custom_order_carrier.shipping_budget',
                        $prefix . 'custom_order_carrier.width',
                        $prefix . 'custom_order_carrier.height',
                        $prefix . 'custom_order_carrier.depth',
                        $prefix . 'custom_order_carrier.weight'
                    )
                    ->where(      $prefix . 'orders.id_shop', $shopId )
                    ->where(      $prefix . 'orders.reference', $row[1] )
                    ->where(      $prefix . 'order_carrier.tracking_number', $row[0] )
                    ->first();
                    
            }elseif($by_id_order == 'NACEX'){
                
                $shippment = DB::connection('mysql2')->table($prefix . 'orders')
                    ->join(       $prefix . 'order_carrier',     $prefix . 'orders.id_order', '=', $prefix . 'order_carrier.id_order')
                    ->leftJoin(   $prefix . 'custom_order_carrier', $prefix . 'order_carrier.id_order_carrier', '=', $prefix . 'custom_order_carrier.id_order_carrier')
                    ->select(
                        $prefix . 'orders.id_order',
                        $prefix . 'custom_order_carrier.shipping_budget',
                        $prefix . 'custom_order_carrier.width',
                        $prefix . 'custom_order_carrier.height',
                        $prefix . 'custom_order_carrier.depth',
                        $prefix . 'custom_order_carrier.weight'
                    )
                    ->where(      $prefix . 'orders.id_shop', $shopId )
                    ->where(      $prefix . 'orders.id_order', str_replace('pedido_', '', $row[6]) )
                    ->where(      $prefix . 'order_carrier.tracking_number', 'LIKE', "%" . str_replace('7490/', '', $row[4]) . '%' )
                    ->first();

            }elseif($by_id_order == 'UPS'){
                $shippment = DB::connection('mysql2')->table($prefix . 'orders')
                    ->join(       $prefix . 'order_carrier',     $prefix . 'orders.id_order', '=', $prefix . 'order_carrier.id_order')
                    ->leftJoin(   $prefix . 'custom_order_carrier', $prefix . 'order_carrier.id_order_carrier', '=', $prefix . 'custom_order_carrier.id_order_carrier')
                    ->select(
                        $prefix . 'orders.id_order',
                        $prefix . 'custom_order_carrier.shipping_budget',
                        $prefix . 'custom_order_carrier.width',
                        $prefix . 'custom_order_carrier.height',
                        $prefix . 'custom_order_carrier.depth',
                        $prefix . 'custom_order_carrier.weight'
                    )
                    ->where(      $prefix . 'orders.id_shop', $shopId )
                    ->where(      $prefix . 'order_carrier.tracking_number', 'LIKE', '%' . $row[4] . '%' )
                    ->first();
            }
            
            
            if(!is_null($shippment)){
                $row['store'] = $store;
                $row['id_order'] = $shippment->id_order;
                $row['weight'] = round((float) ($shippment->weight ?? 0), 2);
                $row['width'] = round((float) ($shippment->width ?? 0), 2);
                $row['height'] = round((float) ($shippment->height ?? 0), 2);
                $row['length'] = round((float) ($shippment->depth ?? 0), 2);
                $row['value'] = $shippment->shipping_budget ?? 0;
    
                $shop_rows[] =$row;
            }
        }

        return $shop_rows;
    }

    private function requestDataFromASD($rows, $by_carrier = '') {
        return self::requestDataFromShop($rows, $by_carrier, 3, 'ASD');

    }

}
