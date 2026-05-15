<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\CustomTools\mailsController;

use App\Models\prestashop\asm_dashboard;
use App\Models\prestashop\product;
use App\Models\prestashop\manufacturers;
use App\Models\prestashop\orders;
use App\Models\prestashop\order_carrier;
use App\Models\prestashop\stock_available;
use App\Models\prestashop\asm_email_alert;

use App\Models\modules\dashboard\dashboard;

class dashboardController extends Controller{

    public function __construct(){ $this->middleware('auth'); }

    public function index(){

        $data = [
            'breadcrumbs'   => [ 'name' =>  trans('Dashboard'), 'url' => route('dashboard.index')],
            'manufacturers' => manufacturers::getManufacturersForSelect()
        ];

        return View::make('areas/dashboard/index')->with($data);
    }
        
    public function post(Request $request)
    {
        $sold = [];
    
        if ($request->action == 'getSalesHistory') {
    
            $prefix = env('DB2_DB_prefix', 'ps_');
            $psDb = DB::connection('mysql2');
    
            $ordersTable = $prefix . 'orders';
            $orderDetailTable = $prefix . 'order_detail';
            $productTable = $prefix . 'product';
            $productAttributeTable = $prefix . 'product_attribute';
            $stockTable = $prefix . 'stock_available';
    
            $details = $psDb->table($ordersTable . ' as orders')
                ->select([
                    'order_detail.product_id',
                    'order_detail.product_attribute_id',
                    'product.reference as reference',
                    'product_attribute.reference as attr_reference',
                    DB::raw('SUM(order_detail.product_quantity) as sold'),
                ])
                ->leftJoin($orderDetailTable . ' as order_detail', 'order_detail.id_order', '=', 'orders.id_order')
                ->leftJoin($productTable . ' as product', 'product.id_product', '=', 'order_detail.product_id')
                ->leftJoin($productAttributeTable . ' as product_attribute', 'product_attribute.id_product_attribute', '=', 'order_detail.product_attribute_id')
                ->where('orders.date_add', '>', $request->date)
                ->whereIn('orders.current_state', [2, 3, 4, 5, 15, 16, 28])
                ->where('product.id_manufacturer', $request->brand)
                ->groupBy(
                    'order_detail.product_id',
                    'order_detail.product_attribute_id',
                    'product.reference',
                    'product_attribute.reference'
                )
                ->get();
    
            $stockRows = $psDb->table($stockTable . ' as stock_available')
                ->select([
                    'stock_available.id_product',
                    'stock_available.id_product_attribute',
                    DB::raw('SUM(stock_available.quantity) as stock'),
                ])
                ->groupBy(
                    'stock_available.id_product',
                    'stock_available.id_product_attribute'
                )
                ->get();
    
            $stockMap = [];
            foreach ($stockRows as $row) {
                $stockKey = $row->id_product . '_' . $row->id_product_attribute;
                $stockMap[$stockKey] = (int) $row->stock;
            }
    
            foreach ($details as $product) {
                $stockKey = $product->product_id . '_' . $product->product_attribute_id;
                $stock = $stockMap[$stockKey] ?? 0;
    
                $key = strlen((string)$product->attr_reference) > 0 ? $product->attr_reference : $product->reference;
    
                $sold[$key] = [
                    'reference' => $product->reference,
                    'reference_attr' => $product->attr_reference,
                    'stock' => $stock,
                    'sold' => (int)$product->sold,
                ];
            }
    
            $columns = ['REFERENCE', 'ATTRIBUTE', 'STOCK', 'SOLD'];
            $file = fopen(public_path() . '/admin/download/sold.csv', 'w');
            fputcsv($file, $columns, ';');
    
            foreach ($sold as $item) {
                fputcsv($file, [
                    $item['reference'],
                    $item['reference_attr'],
                    $item['stock'],
                    $item['sold']
                ], ';');
            }
    
            fclose($file);
            $url = '/admin/download/sold.csv';
            $viewRendered = view('areas/dashboard/includes/sold', compact('sold', 'url'))->render();
            return response()->json(['html' => $viewRendered]);
    
        } elseif ($request->action == 'requestedProductSendEmail') {
    
            $requested_product = asm_email_alert::find($request->id);
    
            $product_detail = product::with('lang')
                ->where('id_product', $requested_product->id_product)
                ->first();
    
            $data = [
                'requested' => $requested_product,
                'product_info' => $product_detail
            ];
    
            /** Faz o envio do email de notificação **/
            $email = new mailsController();
            $html = $email->createStructure(
                'ASM',
                'requestedProducts',
                trans('mails.Requested products notification'),
                $data,
                $requested_product->id_lang
            );
            $email->send(
                $requested_product->email,
                $html,
                trans('mails.Requested products notification')
            );
    
            /** Remove o registo de pedido de notificação do produto da base de dados **/
            asm_email_alert::where('id', $request->id)->delete();
    
            return response()->json(['result' => 'success']);
    
        } elseif ($request->action == 'removeItem') {
    
            asm_email_alert::where('id', $request->id)->delete();
    
            return response()->json(['result' => 'success']);
    
        } elseif ($request->action == 'addException') {
    
            asm_dashboard::addException($request);
    
            return response()->json(['result' => 'success']);
        }
    }

    public function shipping_report(){
        
        $carrier_data = [];
        $carrier_data['DPD'] = 0;
        $carrier_data['UPS'] = 0;
        $carrier_data['TNT'] = 0;
        $carrier_data['NACEX'] = 0;
        $carrier_data['FEDEX'] = 0;
        $carrier_data['GLS'] = 0;
    
        $totalPaidByCustomer = orders::shippingPaidByCustomer(date('Y'));
        $totalByOrder = order_carrier::shippingByOrder(date('Y'));
    
        return response()->json([
            'html' => view('areas/dashboard/includes/shipping_report', compact('totalPaidByCustomer', 'totalByOrder'))->render()
        ]);
    }

}
