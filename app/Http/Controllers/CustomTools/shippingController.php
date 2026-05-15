<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\DB;


use App\Models\modules\shipping\shipping;
use App\Models\modules\shipping\shipping_package;
use App\Models\modules\shipping\shipping_delay;
use App\Models\modules\shipping_erp\shipping_erp;
use App\Models\modules\supplier_map\supplier_map;

use App\Models\prestashop\suppliers;


class shippingController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function index(){
        
        $this->breadcrumbs[] = [ 'name' =>  trans('Logistics'), 'url' => route('logistics.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('IMPORTATIONS STATS'), 'url' => route('shipping.index')];
        
        $data = [
            'actions'    => [],
            'breadcrumbs'=> $this->breadcrumbs,
            'shipments_waiting'    => shipping::getShipements(1),
            'shipments_in_transit' => shipping::getShipements(2),
            'shipments_received'   => shipping::getShipements(3, 'delivery_date'),
            'shipments_cancelled'  => shipping::getShipements(4),
            'suppliers'  => suppliers::orderBy('name', 'ASC')->pluck('name', 'id_supplier'),
            'carriers'  => shipping::getCarriers()
        ];
        
        return View::make('customTools/shipping/index')->with($data);
    }
    
    public function add(){
        
        $this->breadcrumbs[] = [ 'name' =>  trans('Logistics'), 'url' => route('logistics.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('IMPORTATIONS STATS'), 'url' => route('shipping.index')];
        
        $data = [
            'actions'    => [],
            'breadcrumbs'=> $this->breadcrumbs,
            'suppliers'  => suppliers::orderBy('name', 'ASC')->pluck('name', 'id_supplier'),
            'carriers'  => shipping::getCarriers()
        ];
        
        return View::make('customTools/shipping/add')->with($data);
    }

    public function store(Request $request){
        shipping::create($request);
        return redirect()->route('shipping.index');
    }
    
    public function edit($id){
        
        $this->breadcrumbs[] = [ 'name' =>  trans('Logistics'), 'url' => route('logistics.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('IMPORTATIONS STATS'), 'url' => route('shipping.index')];
        
        $data = [
            'actions'    => [],
            'breadcrumbs'=> $this->breadcrumbs,
            'suppliers'  => suppliers::orderBy('name', 'ASC')->pluck('name', 'id_supplier'),
            'carriers'   => shipping::getCarriers(),
            'shipment'   => shipping::getShipment($id),
            'packages'   => shipping_package::getPackages($id),
            'erp'   => shipping_erp::getOrders($id),
            'delay_dates'   => shipping_delay::getDelays($id)
        ];
        
        return View::make('customTools/shipping/edit')->with($data);
    }

    public function update($id, Request $request){
        shipping::updateData($id, $request);
        return redirect()->route('shipping.index');
    }
    
    public function addDelay(Request $request){
        return shipping_delay::addNewDelay($request->id_shipping, $request->newEta);
    }
    
    public function downloadData(Request $request){
        
        $data = shipping::getShipements($request->status);
        
        switch ($request->status) {
            case 1:
                $title = 'Waiting_' . date('YmdHis');
                break;
            case 2:
                $title = 'In_Transit_' . date('YmdHis');
                break;
            case 3:
                $title = 'Received_' . date('YmdHis');
                break;
            case 4:
                $title = 'Cancelled_' . date('YmdHis');
                break;
            default:
                $title = 'shipments_' . date('YmdHis');
                break;
        }
    
        $fileName = $title . '.csv';
        $filePath = 'logistics/shipments/' . $fileName;
    
        $header = [ "id", "supplier", "carrier_name", "invoice", "invoice_number", "ready_date", "validation_date", "shipping_quote", "packing_list", "customs_docs", "tracking", "status", "id_claim", "picking_date", "carrier", "incoterm", "route", "freight", "customs_clear", "import_duties", 'delay_date_1', 'delay_date_2', 'delay_date_3', 'delay_date_4', 'delay_date_5', 'delay_date_6', "delivery_date", "comments" ];
        $header_names = [ "ID", "SUPPLIER", "CARRIER NAME", "INVOICE", "INVOICE NUMBER", "READY DATE", "VALIDATION DATE", "SHIPPING QUOTE", "PACKING LIST", "CUSTOMS DOCS", "TRACKING", "STATUS", "id_claim", "PICKING DATE", "CARRIER", "INCOTERM", "ROUTE", "FREIGHT", "CUSTOMS CLEAR", "IMPORT DUTIES", "1º DELAY DATE", "2º DELAY DATE", "3º DELAY DATE", "4º DELAY DATE", "5º DELAY DATE", "6º DELAY DATE", "DELIVERY DATE", "COMMENTS" ];

        $csvData = fopen('php://temp', 'r+');

        fwrite($csvData, "\xEF\xBB\xBF"); 

        fputcsv($csvData, $header_names, ';');
    
        foreach ($data as $row) {
            $row_data = [];
            
            $delays = shipping_delay::getDelays($row->id);
            
            foreach ($header as $header_item) {
                
                $delay_fields = explode('delay_date_', $header_item);
                
                if( str_contains( $header_item, 'delay_date_') ){
                    
                    $index = $delay_fields[1] - 1;
                    
                    $row_data[] = ( isset($delays[$index]) ) ? $delays[ $index ]->date : '';
                }else{
                    $row_data[] = $row->$header_item ?? '';
                }
                
            }
            fputcsv($csvData, $row_data, ';');
        }
    
        rewind($csvData);
        $csvString = stream_get_contents($csvData);
        fclose($csvData);

        Storage::disk('public_uploads')->put($filePath, $csvString);

        return response()->json(['file' => $filePath]);
    }

    public function packingList(Request $request)
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('Logistics'), 'url' => route('logistics.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('IMPORTATIONS STATS'), 'url' => route('shipping.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('Packing list'), 'url' => '#'];
        
        $erpInput = $request->input('erp', []);
        $poIds = [];
        
        $oneOrderPOID = 0;
        if (is_array($erpInput)) {
            foreach ($erpInput as $v) {
                if (is_numeric($v) && (int)$v > 0) {
                    $poIds[] = (int)$v;
                    $oneOrderPOID = $v;
                }
            }
        }
        
        $poIds = array_values(array_unique($poIds));

        if (empty($poIds)) {
            return back()->with('error', 'Nenhum PO ID válido foi indicado.');
        }
        
        $po = DB::table('oms_billed_orders as bo')
            ->join('oms_order_notes as onote', 'onote.id', '=', 'bo.order_note_id')
            ->select('onote.supplier_id')
            ->where('bo.id', $oneOrderPOID)
            ->first();
        
        $supplier = null;
        $supplier_map = null;
        
        if (isset($po->supplier_id)) {

            $supplier_map = supplier_map::where('id_supplier', $po->supplier_id)
                ->select([
                    'id_supplier',
                    'address',
                    'country',
                    'email',
                    'phone',
                ])
                ->first();

            $supplier = suppliers::where('id_supplier', $po->supplier_id)->first();
            
        }
        
        $ps = env('DB2_DB_prefix', env('DB2_prefix', 'ps_'));
        $ps = str_contains($ps, '.') ? $ps : config('database.connections.mysql2.database') . '.' . $ps;

        $receivedSubquery = DB::table('oms_reception_lines')
            ->select('billed_order_line_id', DB::raw('SUM(qty_received) as qty_received_sum'))
            ->groupBy('billed_order_line_id');

        $rows = DB::table('oms_billed_order_lines as bol')
            ->join($ps . 'product as p', 'p.id_product', '=', 'bol.product_id')
            ->leftJoin($ps . 'manufacturer as m', 'm.id_manufacturer', '=', 'p.id_manufacturer')
            ->leftJoin($ps . 'product_attribute as pa', 'pa.id_product_attribute', '=', 'bol.product_attribute_id')
            ->leftJoin($ps . 'product_lang as pl', function ($join) {
                $join->on('pl.id_product', '=', 'p.id_product')
                    ->where('pl.id_lang', '=', 1)
                    ->where('pl.id_shop', '=', 1);
            })
            ->leftJoin($ps . 'custom_product as cp', 'cp.id_product', '=', 'p.id_product')
            ->leftJoin($ps . 'custom_product_attribute as cpa', 'cpa.id_product_attribute', '=', 'pa.id_product_attribute')
            ->leftJoinSub($receivedSubquery, 'rl_sum', 'rl_sum.billed_order_line_id', '=', 'bol.id')
            ->whereIn('bol.billed_order_id', $poIds)
            ->whereRaw('COALESCE(bol.qty_billed, 0) > COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0)')
            ->groupBy(
                DB::raw('COALESCE(pa.reference, p.reference, "")'),
                DB::raw('COALESCE(pl.name, "")'),
                'cp.nc',
                'p.weight',
                'p.depth',
                'p.width',
                'p.height',
                'm.currency',
                'bol.product_attribute_id',
                'pa.wholesale_price',
                'p.wholesale_price',
                'cpa.wholesale_price_base_currency',
                'cp.wholesale_price_base_currency'
            )
            ->select([
                DB::raw('COALESCE(pa.reference, p.reference, "") as referencia'),
                DB::raw('COALESCE(pl.name, "") as name'),
                DB::raw('cp.nc as hs_code'),
                DB::raw("COALESCE(cpa.wholesale_price_base_currency, pa.wholesale_price, cp.wholesale_price_base_currency, p.wholesale_price, 0) AS wholesale_price"),
                'p.weight',
                DB::raw('p.depth as comprimento'),
                'p.width as largura',
                'p.height as altura',
                DB::raw('SUM(COALESCE(bol.qty_billed, 0) - COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0)) as quantidade'),
                DB::raw('SUM(COALESCE(bol.qty_billed, 0) - COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0)) * COALESCE(cpa.wholesale_price_base_currency, pa.wholesale_price, cp.wholesale_price_base_currency, p.wholesale_price, 0) AS row_price'),
            ])
            ->orderBy('referencia')
            ->get();

            $data = $request->all();
            $packages = $data['package'] ?? [];
            $types    = $packages['type'] ?? [];
            
            $lines = [];
            
            foreach ($types as $i => $typeRaw) {
            
                $typeRaw = trim((string) $typeRaw);
                if ($typeRaw === '') continue;

                if ($typeRaw === 'container_20') {
                    $type = 'Container 20';
                } elseif ($typeRaw === 'container_40') {
                    $type = 'Container 40';
                } else {
                    $type = ucfirst($typeRaw); 
                }
            
                $quantity = $packages['quantity'][$i] ?? null;
                $width    = $packages['width'][$i]    ?? null;
                $height   = $packages['height'][$i]   ?? null;
                $depth    = $packages['depth'][$i]    ?? null;
                $weight   = $packages['weight'][$i]   ?? null;

                if (is_null($quantity)) continue;
            
                $val = function ($v) {
                    return ($v === null || $v === '') ? null : (string) $v;
                };
            
                $quantity = $val($quantity);
                $width    = $val($width);
                $height   = $val($height);
                $depth    = $val($depth);
                $weight   = $val($weight);
            
                $parts = [];
                $parts[] = $quantity . ' * ' . $type;
            
                // Se for container não tem medidas
                if (!str_starts_with($typeRaw, 'container')) {
                    if ($width !== null && $height !== null && $depth !== null) {
                        $parts[] = "{$width} x {$height} x {$depth} (cm)";
                    } else {
                        $parts[] = " NO MEASURES ";
                    }
                } else {
                    $parts[] = " NO MEASURES ";
                }
            
                $parts[] = ($weight ?? '-') . " (kg)";
            
                $lines[] = implode(' | ', $parts);
            }
            
            $formattedText = implode("\n", $lines);

        return view('customTools.shipping.includes.packingList', [
            'poIds' => $poIds,
            'rows'  => $rows,
            'breadcrumbs'=> $this->breadcrumbs,
            'formattedText' => $formattedText,
            'supplier' => $supplier,
            'supplier_map' => $supplier_map
        ]);
    }
    
    public function exportPackingListXls(Request $request)
    {
        $items = array_values($request->input('items', []));
        if (!$items) return back()->with('error', 'Sem dados para exportar.');
    
        $shipper  = $request->input('shipper', []);
        $receiver = $request->input('receiver', []);
        $shippingDetail = (string)$request->input('shippingDetail', '');
        $shippingDetail = str_replace(["\r\n", "\r"], "\n", $shippingDetail);

        $totals   = $request->input('totals', []);
    
        $totalWeight = isset($totals['total_weight']) ? (float)$this->toNumber($totals['total_weight']) : 0;
        $totalQty    = isset($totals['total_qty']) ? (float)$this->toNumber($totals['total_qty']) : 0;
        $totalPrice  = isset($totals['total_price']) ? (float)$this->toNumber($totals['total_price']) : 0;
    
        $xml  = '<?xml version="1.0"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
            xmlns:o="urn:schemas-microsoft-com:office:office"
            xmlns:x="urn:schemas-microsoft-com:office:excel"
            xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
        $xml .= '<Worksheet ss:Name="PackingList"><Table>' . "\n";

        // Cabeçalhos dos blocos
        $xml .= '<Row>' . $this->xlsRow([
            'SHIPPER', '',
            '', // coluna vazia
            'RECEIVER', '',
            '', // coluna vazia
            'SHIPPING DETAILS'
        ]) . '</Row>' . "\n";
        
        // Company
        $xml .= '<Row>' . $this->xlsRow([
            'Company', $shipper['company'] ?? '',
            '',
            'Company', $receiver['company'] ?? '',
            '',
            $shippingDetail
        ]) . '</Row>' . "\n";
        
        // Address
        $xml .= '<Row>' . $this->xlsRow([
            'Address', $shipper['address'] ?? '',
            '',
            'Address', $receiver['address'] ?? '',
            '',
            ''
        ]) . '</Row>' . "\n";
        
        // City
        $xml .= '<Row>' . $this->xlsRow([
            'City', $shipper['city'] ?? '',
            '',
            'City', $receiver['city'] ?? '',
            '',
            ''
        ]) . '</Row>' . "\n";
        
        // Country
        $xml .= '<Row>' . $this->xlsRow([
            'Country', $shipper['country'] ?? '',
            '',
            'Country', $receiver['country'] ?? '',
            '',
            ''
        ]) . '</Row>' . "\n";
        
        // EORI
        $xml .= '<Row>' . $this->xlsRow([
            'EORI', $shipper['eori'] ?? '',
            '',
            'EORI', $receiver['eori'] ?? '',
            '',
            ''
        ]) . '</Row>' . "\n";
        
        // VAT
        $xml .= '<Row>' . $this->xlsRow([
            'VAT', $shipper['vat'] ?? '',
            '',
            'VAT', $receiver['vat'] ?? '',
            '',
            ''
        ]) . '</Row>' . "\n";
        
        $xml .= '<Row></Row>' . "\n";
        $xml .= '<Row></Row>' . "\n";

    
        // ====== TABELA ======
        $headers = [
            'REFERENCE',
            'NAME',
            'HS CODE',
            'WEIGHT (kg)',
            'WIDTH (cm)',
            'HEIGHT (cm)',
            'DEPTH (cm)',
            'PRICE',
            'QTY',
            'TOTAL ROW'
        ];
        $xml .= '<Row>' . $this->xlsRow($headers) . '</Row>' . "\n";
    
        foreach ($items as $it) {
            $row = [
                $it['referencia'] ?? '',
                $it['name'] ?? '',
                $it['hs_code'] ?? '',
                $it['weight'] ?? '',
                $it['comprimento'] ?? '',
                $it['largura'] ?? '',
                $it['altura'] ?? '',
                $it['wholesale_price'] ?? '',
                $it['quantidade'] ?? '',
                $it['row_price'] ?? '',
            ];
            $xml .= '<Row>' . $this->xlsRow($row) . '</Row>' . "\n";
        }
    
        // ====== TOTAIS ======
        $xml .= '<Row></Row>' . "\n";
        $xml .= '<Row>' . $this->xlsRow([
            'TOTALS',
            '',
            '',
            number_format($totalWeight, 2, '.', ''),
            '',
            '',
            '',
            '',
            number_format($totalQty, 0, '.', ''),
            number_format($totalPrice, 2, '.', '')
        ]) . '</Row>' . "\n";
    
        $xml .= '</Table></Worksheet></Workbook>';
    
        $filename = 'packing_list_' . date('Ymd_His') . '.xls';
    
        return response($xml, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
    
    private function toNumber($v)
    {
        // aceita "123.45", "123,45", "123.45 €"
        $v = (string)$v;
        $v = str_replace(['€', ' '], '', $v);
        $v = str_replace(',', '.', $v);
        return is_numeric($v) ? $v : 0;
    }

    
    private function xlsRow(array $cells): string
    {
        $out = '';
        foreach ($cells as $v) {
            $v = htmlspecialchars((string)$v, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $out .= '<Cell><Data ss:Type="String">'.$v.'</Data></Cell>';
        }
        return $out;
    }
    public function exportPackingListCsv(Request $request)
    {
        $items = array_values($request->input('items', []));
        if (!$items) return back()->with('error', 'Sem dados para exportar.');
    
        $shipper  = $request->input('shipper', []);
        $receiver = $request->input('receiver', []);
        $totals   = $request->input('totals', []);
    
        $totalWeight = isset($totals['total_weight']) ? $this->toNumber($totals['total_weight']) : '';
        $totalQty    = isset($totals['total_qty']) ? $this->toNumber($totals['total_qty']) : '';
        $totalPrice  = isset($totals['total_price']) ? $this->toNumber($totals['total_price']) : '';
    
        $filename = 'packing_list_' . date('Ymd_His') . '.csv';
    
        $callback = function() use ($items, $shipper, $receiver, $totalWeight, $totalQty, $totalPrice) {
            $out = fopen('php://output', 'w');
    
            // BOM para Excel reconhecer UTF-8
            fwrite($out, "\xEF\xBB\xBF");
    
            $sep = ';';
    
            // ====== TOPO: SHIPPER / RECEIVER ======
            fputcsv($out, ['SHIPPER'], $sep);
            fputcsv($out, ['Company',  $shipper['company']  ?? ''], $sep);
            fputcsv($out, ['Address',  $shipper['address']  ?? ''], $sep);
            fputcsv($out, ['City',     $shipper['city']     ?? ''], $sep);
            fputcsv($out, ['Country',  $shipper['country']  ?? ''], $sep);
            fputcsv($out, ['EORI',     $shipper['eori']     ?? ''], $sep);
            fputcsv($out, ['VAT',      $shipper['vat']      ?? ''], $sep);
    
            fputcsv($out, [''], $sep);
    
            fputcsv($out, ['RECEIVER'], $sep);
            fputcsv($out, ['Company',  $receiver['company'] ?? ''], $sep);
            fputcsv($out, ['Address',  $receiver['address'] ?? ''], $sep);
            fputcsv($out, ['City',     $receiver['city']    ?? ''], $sep);
            fputcsv($out, ['Country',  $receiver['country'] ?? ''], $sep);
            fputcsv($out, ['EORI',     $receiver['eori']    ?? ''], $sep);
            fputcsv($out, ['VAT',      $receiver['vat']     ?? ''], $sep);
    
            fputcsv($out, [''], $sep);
            fputcsv($out, [''], $sep);
    
            // ====== TABELA ======
            fputcsv($out, [
                'REFERENCE',
                'NAME',
                'HS CODE',
                'WEIGHT (kg)',
                'WIDTH (cm)',
                'HEIGHT (cm)',
                'DEPTH (cm)',
                'PRICE',
                'QTY',
                'TOTAL ROW'
            ], $sep);
    
            foreach ($items as $it) {
                fputcsv($out, [
                    $it['referencia'] ?? '',
                    $it['name'] ?? '',
                    $it['hs_code'] ?? '',
                    $it['weight'] ?? '',
                    $it['comprimento'] ?? '',
                    $it['largura'] ?? '',
                    $it['altura'] ?? '',
                    $it['wholesale_price'] ?? '',
                    $it['quantidade'] ?? '',
                    $it['row_price'] ?? '',
                ], $sep);
            }
    
            // ====== TOTAIS ======
            fputcsv($out, [''], $sep);
            fputcsv($out, [
                'TOTALS',
                '',
                '',
                $totalWeight,
                '',
                '',
                '',
                '',
                $totalQty,
                $totalPrice
            ], $sep);
    
            fclose($out);
        };
    
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    
}
