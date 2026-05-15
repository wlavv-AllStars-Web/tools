<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use App\Models\modules\shipmentsCheck\CarrierExpeditionCheck;

class CarrierExpeditionCheckController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('Logistics'), 'url' => route('logistics.index')];
    }

    public function index()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('customTools.shipments.index'), 'url' => route('customTools.shipments.index')];

        $shipments = DB::connection('mysql2')->select("
            SELECT c.name as carrier_name, COUNT(oc.id_order) as total
            FROM ps_order_carrier oc
            JOIN ps_orders o ON o.id_order = oc.id_order
            JOIN ps_carrier c ON c.id_carrier = oc.id_carrier
            JOIN ps_order_history oh ON oh.id_order = o.id_order
            WHERE oh.id_order_state = 4
            AND DATE(oh.date_add) = CURDATE()
            GROUP BY c.name
        ");

        $data = [
            'counters'      => [],
            'panels'        => [],
            'accessList'    => null,
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs,
            'shipments' => $shipments
        ];
        
        return View::make('customTools/shipments-check/index')->with($data);
    }

    public function store(Request $request)
    {
        foreach ($request->carrier as $key => $carrier) {

            CarrierExpeditionCheck::create([
                'carrier_name'  => $carrier,
                'shipments'     => $request->shipments[$key],
                'non_standard'  => $request->non_standard[$key] ?? 0,
                'qty_checked'   => $request->qty_checked[$key] ?? 0,
                'note'          => $request->note[$key] ?? null,
                'user_id'       => auth()->id(),
                'check_date'    => now()->toDateString()
            ]);
        }

        Mail::raw('Shipment check realizado.', function ($msg) {
            $msg->to('bruno.fernandes.asm@gmail.com')
                ->subject('Shipment Check');
        });

        return back()->with('success', 'Registo guardado');
    }
    
    public function history(Request $request)
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('customTools.shipments.index'), 'url' => route('customTools.shipments.index')];

        $query = CarrierExpeditionCheck::query();
    
        if ($request->year) {
            $query->whereYear('check_date', $request->year);
        }
    
        if ($request->month) {
            $query->whereMonth('check_date', $request->month);
        }
    
        $checks = $query
            ->orderBy('check_date', 'desc')
            ->orderBy('carrier_name')
            ->get()
            ->groupBy('check_date') // 🔥 AGRUPAR POR DATA
            ->map(function ($day) {
    
                return $day->map(function ($item) {
    
                    $item->has_diff = ($item->qty_checked != $item->shipments);
    
                    return $item;
                });
    
            });

        $data = [
            'counters'      => [],
            'panels'        => [],
            'accessList'    => null,
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs,
            'checks'        => $checks
        ];
        
        return View::make('customTools/shipments-check/history')->with($data);
    }
        
    public function exportCsv(Request $request)
    {
        $query = CarrierExpeditionCheck::query();
    
        if ($request->year) {
            $query->whereYear('check_date', $request->year);
        }
    
        if ($request->month) {
            $query->whereMonth('check_date', $request->month);
        }
    
        $data = $query->orderBy('check_date', 'desc')->get();
    
        $filename = 'shipments_check_' . date('Ymd_His') . '.csv';
    
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ];
    
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
    
            fputcsv($file, [
                'Date',
                'Carrier',
                'Shipments',
                'Checked',
                'Non Standard',
                'Note',
                'User'
            ]);
    
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->check_date,
                    $row->carrier_name,
                    $row->shipments,
                    $row->qty_checked,
                    $row->non_standard,
                    $row->note,
                    $row->user_id
                ]);
            }
    
            fclose($file);
        };
    
        return response()->stream($callback, 200, $headers);
    }    
}