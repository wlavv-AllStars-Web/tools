<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class ReturnWarrantyAvailabilityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $orderId = (int) $request->query('order_id');
        $order = $orderId > 0 ? $this->findOrder($orderId) : null;

        return View::make('customTools.return-warranty-availability.index', [
            'breadcrumbs' => [
                ['name' => 'Web', 'url' => route('web.tools.return_warranty.index')],
                ['name' => 'Return / Warranty availability', 'url' => route('web.tools.return_warranty.index'), 'no_translation' => true],
            ],
            'orderId' => $orderId,
            'order' => $order,
            'details' => $order ? $this->orderDetails($orderId) : collect(),
            'shipments' => $order ? $this->shipments($orderId) : collect(),
            'trackings' => $order ? $this->trackings($orderId) : collect(),
        ]);
    }

    public function enable(Request $request): RedirectResponse
    {
        $data = $request->validate(['order_id' => ['required', 'integer', 'min:1']]);
        $orderId = (int) $data['order_id'];

        if (!$this->findOrder($orderId)) {
            return redirect()->route('web.tools.return_warranty.index', ['order_id' => $orderId])
                ->with('error', 'A encomenda indicada não existe.');
        }

        $this->assertAvailabilityColumnsExist();
        DB::connection('mysql2')->table($this->psTable('custom_orders'))->updateOrInsert(
            ['id_order' => $orderId],
            [
                'return_warranty_enabled' => 1,
                'return_warranty_enabled_at' => now(),
                'return_warranty_enabled_by' => (int) auth()->id(),
            ]
        );

        return redirect()->route('web.tools.return_warranty.index', ['order_id' => $orderId])
            ->with('success', 'A encomenda foi disponibilizada manualmente para devolução e garantia.');
    }

    private function findOrder(int $orderId): ?object
    {
        $orders = $this->psTable('orders');
        $customers = $this->psTable('customer');
        $states = $this->psTable('order_state_lang');
        $customOrders = $this->psTable('custom_orders');

        return DB::connection('mysql2')->table($orders . ' as o')
            ->leftJoin($customers . ' as c', 'c.id_customer', '=', 'o.id_customer')
            ->leftJoin($states . ' as osl', function ($join) {
                $join->on('osl.id_order_state', '=', 'o.current_state')->where('osl.id_lang', '=', 2);
            })
            ->leftJoin($customOrders . ' as co', 'co.id_order', '=', 'o.id_order')
            ->where('o.id_order', $orderId)
            ->select([
                'o.id_order', 'o.reference', 'o.date_add', 'o.current_state', 'c.email',
                DB::raw('TRIM(CONCAT(COALESCE(c.firstname, ""), " ", COALESCE(c.lastname, ""))) as customer_name'),
                'osl.name as state_name', 'co.return_warranty_enabled', 'co.return_warranty_enabled_at',
            ])->first();
    }

    private function orderDetails(int $orderId)
    {
        return DB::connection('mysql2')->table($this->psTable('order_detail') . ' as od')
            ->leftJoin($this->psTable('custom_order_detail') . ' as cod', 'cod.id_order_detail', '=', 'od.id_order_detail')
            ->where('od.id_order', $orderId)->orderBy('od.id_order_detail')
            ->select(['od.id_order_detail', 'od.product_reference', 'od.product_name', 'od.product_quantity', 'cod.qtd_sent', 'cod.delivered'])
            ->get();
    }

    private function shipments(int $orderId)
    {
        $shipments = $this->psTable('custom_order_shipment');
        if (!Schema::connection('mysql2')->hasTable($shipments)) {
            return collect();
        }

        return DB::connection('mysql2')->table($shipments . ' as s')
            ->leftJoin($this->psTable('order_detail') . ' as od', 'od.id_order_detail', '=', 's.id_order_detail')
            ->where('s.id_order', $orderId)->orderBy('s.shipped_date')->orderBy('s.cycle_number')
            ->select(['s.id_custom_order_shipment', 's.id_order_detail', 's.cycle_number', 's.id_order_carrier', 's.qty_shipped', 's.shipped_date', 's.delivered', 's.delivery_date', 'od.product_reference', 'od.product_name'])
            ->get();
    }

    private function trackings(int $orderId)
    {
        return DB::connection('mysql2')->table($this->psTable('order_carrier') . ' as oc')
            ->leftJoin($this->psTable('carrier') . ' as c', 'c.id_carrier', '=', 'oc.id_carrier')
            ->where('oc.id_order', $orderId)->whereNotNull('oc.tracking_number')->where('oc.tracking_number', '<>', '')
            ->orderBy('oc.date_add')->select(['oc.id_order_carrier', 'oc.tracking_number', 'oc.date_add', 'c.name as carrier_name'])->get();
    }

    private function assertAvailabilityColumnsExist(): void
    {
        $table = $this->psTable('custom_orders');
        foreach (['return_warranty_enabled', 'return_warranty_enabled_at', 'return_warranty_enabled_by'] as $column) {
            if (!Schema::connection('mysql2')->hasColumn($table, $column)) {
                abort(503, 'A migração da ferramenta Return / Warranty availability ainda não foi executada.');
            }
        }
    }

    private function psTable(string $table): string
    {
        return (string) env('DB2_DB_prefix', env('DB2_prefix', 'ps_')) . $table;
    }
}
