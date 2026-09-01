<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;

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

        return View::make('customTools.return-warranty-availability.fullwidth', [
            'breadcrumbs' => [
                ['name' => 'Web', 'url' => route('web.tools.return_warranty.index')],
                ['name' => 'Return / Warranty availability', 'url' => route('web.tools.return_warranty.index'), 'no_translation' => true],
            ],
            'orderId' => $orderId,
            'order' => $order,
            'details' => $order ? $this->orderDetails($orderId) : collect(),
            'shipments' => $order ? $this->carrierOrHistoryShipments($orderId) : collect(),
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
        DB::connection('mysql2')->transaction(function () use ($orderId) {
            $this->populateCurrentFrontEligibility($orderId);

            $updates = ['parcels_upd' => 1];
            if ($this->hasAvailabilityColumns()) {
                $updates = array_merge($updates, [
                    'return_warranty_enabled' => 1,
                    'return_warranty_enabled_at' => now(),
                    'return_warranty_enabled_by' => (int) auth()->id(),
                ]);
            }

            DB::connection('mysql2')->table($this->psTable('custom_orders'))->updateOrInsert(
                ['id_order' => $orderId],
                $updates
            );
        });

        return redirect()->route('web.tools.return_warranty.index', ['order_id' => $orderId])
            ->with('success', 'A encomenda foi disponibilizada manualmente para devolução e garantia.');
    }

    /**
     * Prepares only dispatched quantities from the selected order for the legacy return frontend.
     * Existing confirmed delivery dates are always preserved.
     */
    private function populateCurrentFrontEligibility(int $orderId): void
    {
        $fallbackShippedAt = $this->carrierOrHistoryShipments($orderId)->first()?->shipped_date;
        $details = DB::connection('mysql2')->table($this->psTable('order_detail') . ' as od')
            ->join($this->psTable('custom_order_detail') . ' as cod', 'cod.id_order_detail', '=', 'od.id_order_detail')
            ->where('od.id_order', $orderId)
            ->lockForUpdate()
            ->select([
                'od.id_order_detail', 'od.product_quantity', 'cod.qtd_sent', 'cod.qtd_sent_first',
                'cod.shipped_date', 'cod.shipped_date_end', 'cod.delivery_date', 'cod.delivery_date_end',
            ])->get();

        $preparedLines = 0;
        $eligibleDetailIds = [];
        foreach ($details as $detail) {
            if ((int) $detail->qtd_sent < 1) {
                continue;
            }

            $firstShippedAt = $this->validDate($detail->shipped_date) ?? $this->validDate($fallbackShippedAt);
            if (!$firstShippedAt) {
                throw ValidationException::withMessages(['order_id' => 'No shipment date was found for this order.']);
            }

            $updates = ['delivered' => 1];
            if (!$this->validDate($detail->delivery_date)) {
                $updates['delivery_date'] = $firstShippedAt->copy()->addDays(5)->format('Y-m-d H:i:s');
            }

            if ((int) $detail->qtd_sent > (int) $detail->qtd_sent_first && !$this->validDate($detail->delivery_date_end)) {
                $lastShippedAt = $this->validDate($detail->shipped_date_end) ?? $firstShippedAt;
                $updates['delivery_date_end'] = $lastShippedAt->copy()->addDays(5)->format('Y-m-d H:i:s');
            }

            DB::connection('mysql2')->table($this->psTable('custom_order_detail'))
                ->where('id_order_detail', (int) $detail->id_order_detail)
                ->update($updates);
            $eligibleDetailIds[] = (int) $detail->id_order_detail;
            $preparedLines++;
        }

        if ($preparedLines === 0) {
            throw ValidationException::withMessages(['order_id' => 'This order has no dispatched lines.']);
        }

        $this->populateShipmentCycleEligibility(
            $orderId,
            $eligibleDetailIds,
            $this->validDate($fallbackShippedAt)
        );
    }

    /**
     * The customer page prioritizes custom_order_shipment over legacy order-detail dates.
     */
    private function populateShipmentCycleEligibility(int $orderId, array $eligibleDetailIds, ?Carbon $fallbackShippedAt): void
    {
        $shipmentsTable = $this->psTable('custom_order_shipment');
        if (!Schema::connection('mysql2')->hasTable($shipmentsTable) || $eligibleDetailIds === []) {
            return;
        }

        $cycles = DB::connection('mysql2')->table($shipmentsTable)
            ->where('id_order', $orderId)
            ->whereIn('id_order_detail', $eligibleDetailIds)
            ->lockForUpdate()
            ->select(['id_custom_order_shipment', 'id_order_detail', 'qty_shipped', 'shipped_date', 'delivery_date'])
            ->get();

        foreach ($cycles as $cycle) {
            if ((int) $cycle->qty_shipped < 1) {
                continue;
            }

            $shippedAt = $this->validDate($cycle->shipped_date) ?? $fallbackShippedAt;
            if (!$shippedAt) {
                throw ValidationException::withMessages([
                    'order_id' => 'No shipment date was found for this shipment cycle.',
                ]);
            }

            $updates = ['delivered' => 1, 'date_upd' => now()];
            if (!$this->validDate($cycle->delivery_date)) {
                $updates['delivery_date'] = $shippedAt->copy()->addDays(5)->format('Y-m-d H:i:s');
            }

            DB::connection('mysql2')->table($shipmentsTable)
                ->where('id_custom_order_shipment', (int) $cycle->id_custom_order_shipment)
                ->where('id_order', $orderId)
                ->where('id_order_detail', (int) $cycle->id_order_detail)
                ->update($updates);
        }
    }

    private function validDate(?string $value): ?Carbon
    {
        if (!$value || str_starts_with($value, '0000-00-00')) {
            return null;
        }

        return Carbon::parse($value);
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

    private function carrierOrHistoryShipments(int $orderId)
    {
        $carrierShipments = DB::connection('mysql2')->table($this->psTable('order_carrier') . ' as oc')
            ->leftJoin($this->psTable('carrier') . ' as c', 'c.id_carrier', '=', 'oc.id_carrier')
            ->where('oc.id_order', $orderId)
            ->orderBy('oc.date_add')
            ->select(['oc.date_add as shipped_date', 'oc.tracking_number', 'c.name as carrier_name', DB::raw("'order_carrier' as source")])
            ->get();

        if ($carrierShipments->isNotEmpty()) {
            return $carrierShipments;
        }

        return DB::connection('mysql2')->table($this->psTable('order_history') . ' as oh')
            ->where('oh.id_order', $orderId)
            ->where('oh.id_order_state', 4)
            ->orderBy('oh.date_add')
            ->select([
                'oh.date_add as shipped_date',
                DB::raw('NULL as tracking_number'),
                DB::raw('NULL as carrier_name'),
                DB::raw("'order_history' as source"),
            ])
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

    private function hasAvailabilityColumns(): bool
    {
        $table = $this->psTable('custom_orders');
        foreach (['return_warranty_enabled', 'return_warranty_enabled_at', 'return_warranty_enabled_by'] as $column) {
            if (!Schema::connection('mysql2')->hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }

    private function psTable(string $table): string
    {
        return (string) env('DB2_DB_prefix', env('DB2_prefix', 'ps_')) . $table;
    }
}
