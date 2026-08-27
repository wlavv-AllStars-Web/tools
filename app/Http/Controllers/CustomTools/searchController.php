<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\prestashop\orders;
use App\Models\prestashop\order_carrier;
use App\Models\prestashop\product;
use App\Models\prestashop\product_attribute;

use App\Models\modules\shipping\shipping;

use App\Models\modules\supplier_delivery_issues\supplier_delivery_issues;
use App\Models\modules\supplier_issues\supplier_issues;
use App\Models\modules\supplier_warranty_issues\supplier_warranty_issues;

use App\Models\modules\carrierIssues\carrierIssues;

use App\Models\modules\documents_manager\documents_manager;
use App\Services\oms\OmsLegacyProcurementService;

class searchController extends Controller
{


    public function globalSearchGet(){
        return redirect()->route('dashboard.index');
    }

    public function globalSearch(Request $request){

        $tag = $request->tag;

        $this->breadcrumbs[] = [ 'name' =>  trans('search'), 'url' => route('search.globalSearch', [ 'tag' => $tag ])];

        $erpOpenOrders = OmsLegacyProcurementService::searchOpenOrders((string) $tag);
        $receivedProducts = OmsLegacyProcurementService::searchReceivedProducts((string) $tag);

        $orders = orders::leftjoin('ps_order_carrier', 'ps_order_carrier.id_order', 'ps_orders.id_order')->leftjoin('ps_order_state', 'ps_orders.current_state', 'ps_order_state.id_order_state')->leftjoin('ps_order_state_lang', 'ps_orders.current_state', 'ps_order_state_lang.id_order_state')->where('ps_order_state_lang.id_lang', 1)->where('ps_orders.id_order', 'like', '%' . $tag . '%')->orWhere('ps_orders.reference', 'LIKE', '"%' . $tag . '%"')->orderBy('ps_orders.id_order', 'DESC')->get();
        $tracking = order_carrier::leftjoin('ps_orders', 'ps_orders.id_order', 'ps_order_carrier.id_order')->leftjoin('ps_order_state', 'ps_orders.current_state', 'ps_order_state.id_order_state')->leftjoin('ps_order_state_lang', 'ps_orders.current_state', 'ps_order_state_lang.id_order_state')->where('tracking_number', 'LIKE', '%' . $tag . '%')->orderBy('ps_orders.id_order', 'DESC')->groupBy('tracking_number')->get();
        $products = product::where('id_product', $tag)->orWhere('reference', 'LIKE', '%' . $tag . '%')->orWhere('ean13', 'LIKE', '%' . $tag . '%' )->orWhere('location', 'LIKE', '%' . $tag . '%' )->get();
        $product_attributes = product_attribute::select('*', 'ps_product_attribute.reference AS ref', DB::raw("'' AS location_attr"), 'ps_product_attribute.ean13 AS ean13_attr')
                                                ->leftjoin('ps_product', 'ps_product.id_product', 'ps_product_attribute.id_product')
                                                ->where('ps_product_attribute.id_product', $tag)
                                                ->where('ps_product_attribute.id_product_attribute', $tag)
                                                ->orWhere('ps_product_attribute.reference',  'like',     '%' . $tag . '%' )

                                                ->orWhere('ps_product_attribute.ean13',      'like',     '%' . $tag . '%' )
                                                ->get();
        $prefix = env('DB2_DB_prefix', env('DB2_prefix', 'ps_'));
        $catalogue = DB::connection('mysql2');
        $like = '%' . $tag . '%';

        $catalogueProducts = $catalogue->table($prefix . 'product as p')
            ->leftJoin($prefix . 'stock_available as sa', function ($join) {
                $join->on('sa.id_product', '=', 'p.id_product')->where('sa.id_product_attribute', 0);
            })
            ->leftJoin($prefix . 'custom_product as cp', 'cp.id_product', '=', 'p.id_product')
            ->where(function ($query) use ($tag, $like) {
                $query->where('p.id_product', (int) $tag)->orWhere('p.reference', 'like', $like)->orWhere('p.ean13', 'like', $like)->orWhere('p.location', 'like', $like);
            })
            ->selectRaw('p.id_product, 0 AS id_product_attribute, p.reference, p.ean13, COALESCE(p.location, "") AS location, COALESCE(sa.quantity, 0) AS stock, COALESCE(cp.stock_arrive, 0) AS stock_arrive')
            ->limit(100)->get();

        $catalogueAttributes = $catalogue->table($prefix . 'product_attribute as pa')
            ->join($prefix . 'product as p', 'p.id_product', '=', 'pa.id_product')
            ->leftJoin($prefix . 'stock_available as sa', function ($join) {
                $join->on('sa.id_product', '=', 'pa.id_product')->on('sa.id_product_attribute', '=', 'pa.id_product_attribute');
            })
            ->leftJoin($prefix . 'custom_product_attribute as cpa', 'cpa.id_product_attribute', '=', 'pa.id_product_attribute')
            ->where(function ($query) use ($tag, $like) {
                $query->where('pa.id_product', (int) $tag)->orWhere('pa.id_product_attribute', (int) $tag)->orWhere('pa.reference', 'like', $like)->orWhere('pa.ean13', 'like', $like);
            })
            ->selectRaw('p.id_product, pa.id_product_attribute, COALESCE(NULLIF(pa.reference, ""), p.reference) AS reference, COALESCE(NULLIF(pa.ean13, ""), p.ean13) AS ean13, COALESCE(p.location, "") AS location, COALESCE(sa.quantity, 0) AS stock, COALESCE(cpa.stock_arrive, 0) AS stock_arrive')
            ->limit(100)->get();

        $catalogueRows = $catalogueProducts->merge($catalogueAttributes)->sortBy(['reference', 'id_product', 'id_product_attribute'])->values();


        $shipments = shipping::where('tracking', 'LIKE', '%' . $tag . '%')->orWhere('invoice_number', 'LIKE', '%' . $tag . '%')->get();

        $supplier_delivery_issues = supplier_delivery_issues::where('po_reference', 'LIKE', '%' . $tag . '%')->orWhere('po_id', 'LIKE', '%' . $tag . '%')->orWhere('reference', 'LIKE', '%' . $tag . '%')->orWhere('comment', 'LIKE', '%' . $tag . '%')->get();
        $supplier_issues = supplier_issues::where('reference', 'LIKE', '%' . $tag . '%')->orWhere('description', 'LIKE', '%' . $tag . '%')->orWhere('info', 'LIKE', '%' . $tag . '%')->get();
        $supplier_warranty_issues = supplier_warranty_issues::where('id_order', 'LIKE', '%' . $tag . '%')->orWhere('reference', 'LIKE', '%' . $tag . '%')->orWhere('description', 'LIKE', '%' . $tag . '%')->orWhere('action', 'LIKE', '%' . $tag . '%')->get();

        $carrierIssues = carrierIssues::where('id_order', 'LIKE', '%' . $tag . '%')->orWhere('tracking', 'LIKE', '%' . $tag . '%')->orWhere('description', 'LIKE', '%' . $tag . '%')->orWhere('issue', 'LIKE', '%' . $tag . '%')->orWhere('carrier', 'LIKE', '%' . $tag . '%')->orWhere('new_tracking', 'LIKE', '%' . $tag . '%')->orWhere('note', 'LIKE', '%' . $tag . '%')->get();

        $documents_manager = documents_manager::where('name', 'LIKE', '%' . $tag . '%')->orWhere('document_number', 'LIKE', '%' . $tag . '%')->orWhere('element', 'LIKE', '%' . $tag . '%')->orWhere('document', 'LIKE', '%' . $tag . '%')->orWhere('notes', 'LIKE', '%' . $tag . '%')->get();

        $data = [
            'actions'           => [],
            'tag'              => $tag,
            'orders'            => $orders,
            'tracking'          => $tracking,
            'products'          => $products,
            'product_attributes'=> $product_attributes,
            'catalogueRows'     => $catalogueRows,
            'shipments'         => $shipments,
            'supplier_delivery_issues' => $supplier_delivery_issues,
            'supplier_issues'   => $supplier_issues,
            'supplier_warranty_issues' => $supplier_warranty_issues,
            'carrierIssues'     => $carrierIssues,
            'documents_manager' => $documents_manager,
            'erpOpenOrders'     => $erpOpenOrders,
            'receivedProducts'  => $receivedProducts
        ];

        return View::make('customTools/search/index')->with($data);

    }
}
