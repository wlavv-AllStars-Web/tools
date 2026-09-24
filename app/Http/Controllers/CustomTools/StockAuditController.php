<?php
namespace App\Http\Controllers\CustomTools;
use App\Http\Controllers\Controller;
use App\Services\StockAudit\StockAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
class StockAuditController extends Controller {
 public function index(Request $request): View {
  $filters=$request->validate(['reference'=>['nullable','string','max:128'],'date'=>['nullable','date'],'from'=>['nullable','date'],'to'=>['nullable','date','after_or_equal:from']]); $movements=collect(); $snapshots=collect();
  if(Schema::hasTable('stock_audit_movements')) { $movements=DB::table('stock_audit_movements')->when(trim((string)($filters['reference']??''))!=='',fn($q)=>$q->where('reference','like','%'.trim($filters['reference']).'%'))->when(!empty($filters['date']),fn($q)=>$q->whereDate('occurred_at',$filters['date']))->when(!empty($filters['from']),fn($q)=>$q->whereDate('occurred_at','>=',$filters['from']))->when(!empty($filters['to']),fn($q)=>$q->whereDate('occurred_at','<=',$filters['to']))->latest('occurred_at')->paginate(100)->withQueryString(); $snapshots=DB::table('stock_audit_snapshots')->latest('captured_at')->limit(20)->get(); }
  return view('customTools.stock-audit.index',compact('movements','snapshots','filters'));
 }
 public function snapshot(StockAuditService $audit): RedirectResponse { $snapshot=$audit->createSnapshot(); return back()->with('success','Backup #'.$snapshot['id'].' criado ('.$snapshot['items_count'].' linhas).'); }
}