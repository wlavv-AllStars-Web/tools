<?php

namespace App\Http\Controllers\Modules\oms;

use App\Http\Controllers\Controller;
use App\Models\modules\oms\BilledOrderLine;
use App\Models\modules\oms\OmsDocumentLineHistory;
use App\Models\modules\oms\OrderNote;
use App\Models\prestashop\suppliers;
use App\Services\Prestashop\PrestashopAdminLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SimplifiedOrderNoteController extends Controller
{
    public function index(Request $request)
    {
        $supplierId = (int) $request->integer('supplier_id');
        $suppliers = suppliers::query()->select(['id_supplier', 'name'])->orderBy('name')->get();
        $orderNotes = $supplierId ? OrderNote::query()->where('supplier_id', $supplierId)->latest()->get(['id','supplier_id','reference','status','created_at']) : collect();
        $orderNote = $orderNotes->firstWhere('id', (int) $request->integer('order_note_id')) ?? $orderNotes->first();
        if ($orderNote) $orderNote->load(['supplier', 'lines']);

        return view('modules.oms.order_notes.simplified', [
            'suppliers' => $suppliers, 'selectedSupplierId' => $supplierId, 'orderNotes' => $orderNotes,
            'orderNote' => $orderNote, 'simplifiedOmsRows' => $orderNote ? $this->rows($orderNote) : collect(),
        ]);
    }

    private function rows(OrderNote $orderNote)
    {
        $lines = $orderNote->lines; $ids = $lines->pluck('id')->filter()->values();
        if ($ids->isEmpty()) return collect();
        $prefix = (string) (env('DB2_prefix') ?: env('DB2_DB_prefix') ?: 'ps_');
        $invoiced = BilledOrderLine::whereIn('order_note_line_id', $ids)->selectRaw('order_note_line_id,SUM(qty_billed) qty')->groupBy('order_note_line_id')->pluck('qty','order_note_line_id');
        $received = DB::table('oms_reception_lines as r')->join('oms_billed_order_lines as b','b.id','=','r.billed_order_line_id')->whereIn('b.order_note_line_id',$ids)->selectRaw('b.order_note_line_id,SUM(r.qty_received) qty')->groupBy('b.order_note_line_id')->pluck('qty','b.order_note_line_id');
        $latest = BilledOrderLine::whereIn('order_note_line_id',$ids)->latest('id')->get()->groupBy('order_note_line_id')->map->first();
        $history = OmsDocumentLineHistory::where('context_type','billed_order_line')->whereIn('context_id',$latest->pluck('id')->filter())->latest('id')->get()->groupBy('context_id')->map->first();
        $productIds = $lines->pluck('product_id')->filter()->unique()->values(); $attrIds = $lines->pluck('product_attribute_id')->filter()->unique()->values();
        $products = DB::connection('mysql2')->table($prefix.'product as p')->leftJoin($prefix.'product_lang as l','l.id_product','=','p.id_product')->leftJoin($prefix.'custom_product as cp','cp.id_product','=','p.id_product')->whereIn('p.id_product',$productIds)->groupBy('p.id_product','p.reference','p.location','cp.dim_verify')->selectRaw('p.id_product,p.reference,p.location housing,COALESCE(cp.dim_verify,0) dim_verify,MIN(l.name) name')->get()->keyBy('id_product');
        $attrs = $attrIds->isEmpty()?collect():DB::connection('mysql2')->table($prefix.'product_attribute as a')->leftJoin($prefix.'custom_product_attribute as ca',function($j){$j->on('ca.id_product_attribute','=','a.id_product_attribute')->on('ca.id_product','=','a.id_product');})->whereIn('a.id_product_attribute',$attrIds)->selectRaw('a.id_product_attribute,a.reference,ca.location housing')->get()->keyBy('id_product_attribute');
        $backorders = $this->backorders($productIds,$prefix);

        return $lines->map(function($line) use($invoiced,$received,$latest,$history,$products,$attrs,$backorders){
            $bl=$latest->get($line->id); $h=$bl?$history->get($bl->id):null; $p=$products->get($line->product_id); $a=$line->product_attribute_id?$attrs->get($line->product_attribute_id):null; $key=(int)$line->product_id.'|'.(int)($line->product_attribute_id??0);
            return ['line_id'=>(int)$line->id,'reference'=>trim((string)($a->reference??$p->reference??''))?:'—','name'=>trim((string)($p->name??''))?:'Product #'.$line->product_id,'housing'=>trim((string)($a->housing??$p->housing??'')),'dim_verified'=>(int)($p->dim_verify??0)===1,'backorders'=>$backorders->get($key,collect())->values(),'ordered'=>(int)$line->qty_ordered,'invoiced'=>(int)($invoiced[$line->id]??0),'received'=>(int)($received[$line->id]??0),'purchase_supplier'=>(float)($bl->unit_price_invoice_currency??0),'purchase_eur'=>(float)($bl->unit_price_eur??0),'sales_supplier'=>(float)($h->new_sale_supplier_currency??0),'sales_eur'=>(float)($h->new_sale_eur??0),'currency_iso'=>(string)($bl->currency_iso??'EUR')];
        });
    }

    private function backorders($productIds, string $prefix)
    {
        return DB::connection('mysql2')->table($prefix.'order_detail as d')->join($prefix.'orders as o','o.id_order','=','d.id_order')->join($prefix.'stock_available as s',function($j){$j->on('s.id_product','=','d.product_id')->on('s.id_product_attribute','=','d.product_attribute_id')->where('s.id_shop',0);})->where('o.current_state',15)->where('s.quantity','<',0)->whereIn('d.product_id',$productIds)->select(['o.id_order','o.reference','o.id_shop','d.product_id','d.product_attribute_id','s.quantity as stock'])->get()->map(function($row){$row->store=(int)$row->id_shop===3?'ASD':'ASM';$row->url=PrestashopAdminLinkService::dashboardOrderAdminUrl((int)$row->id_order,$row->store);return $row;})->groupBy(fn($r)=>(int)$r->product_id.'|'.(int)$r->product_attribute_id);
    }
}
