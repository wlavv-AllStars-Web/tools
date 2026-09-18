<?php
namespace App\Console\Commands;
use App\Services\MoloniVat\MoloniVatValidationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class ReconcileMoloniVatValidationsCommand extends Command {
 protected $signature='moloni-vat:reconcile {--limit=250} {--since=}'; protected $description='Backfill VAT validation tracking from Moloni invoices when a module POST was unavailable.';
 public function handle(MoloniVatValidationService $service):int{$prefix=env('DB2_DB_prefix','ps_');$since=$this->option('since')?:now()->subDays((int)config('moloni_vat.reconciliation_lookback_days',14))->toDateTimeString();
  $rows=DB::connection('mysql2')->table($prefix.'moloni_invoices as mi')->join($prefix.'orders as o','o.id_order','=','mi.order_id')->join($prefix.'address as a','a.id_address','=','o.id_address_invoice')->join($prefix.'country as c','c.id_country','=','a.id_country')->where('mi.invoice_id','>',0)->where('mi.invoice_date','>=',$since)->whereExists(function($q)use($prefix){$q->selectRaw('1')->from($prefix.'customer_group as cg')->whereColumn('cg.id_customer','o.id_customer')->whereIn('cg.id_group',[4,6]);})->select(['o.id_order','o.id_customer','a.vat_number','c.iso_code as country_iso','mi.invoice_id as moloni_invoice_id'])->orderBy('mi.id')->limit(max(1,(int)$this->option('limit')))->get();
  foreach($rows as $row){$groupId=DB::connection('mysql2')->table($prefix.'customer_group')->where('id_customer',$row->id_customer)->whereIn('id_group',[4,6])->orderBy('id_group')->value('id_group');$service->registerInvoice(['id_order'=>(int)$row->id_order,'id_customer'=>(int)$row->id_customer,'customer_group_id'=>(int)$groupId,'country_iso'=>$row->country_iso,'vat_number'=>$row->vat_number,'moloni_invoice_id'=>(int)$row->moloni_invoice_id],'reconciliation');}
  $this->info('Moloni invoice rows reconciled: '.$rows->count());return self::SUCCESS;}
}
