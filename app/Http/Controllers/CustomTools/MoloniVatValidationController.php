<?php
namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Models\modules\moloni_vat_validation\MoloniVatValidation;
use App\Models\modules\moloni_vat_validation\MoloniVatValidationOrder;
use App\Services\MoloniVat\MoloniVatValidationService;
use App\Services\Prestashop\PrestashopAdminLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MoloniVatValidationController extends Controller
{
    public function index(Request $request)
    {
        $status=$request->string('status')->toString();
        $query=MoloniVatValidation::query()->with(['orders'=>fn($q)=>$q->latest('id')])->withCount('orders')->latest('updated_at');
        if($status!=='')$query->where('status',$status);
        $validations=$query->paginate(50)->withQueryString();
        $this->attachOrderMetadata($validations->getCollection());

        return view('customTools.moloni-vat.index',[
            'validations'=>$validations,
            'selectedStatus'=>$status,
            'statuses'=>$this->statuses(),
        ]);
    }

    public function retry(MoloniVatValidation $validation, MoloniVatValidationService $service): RedirectResponse
    {
        $service->retry($validation);
        return back()->with('status','VAT colocado novamente em fila para validação VIES.');
    }

    public function register(Request $request, MoloniVatValidationService $service): JsonResponse
    {
        if(!$this->hasValidToken($request))return response()->json(['success'=>false,'message'=>'Invalid token'],403);
        $data=$request->validate(['id_order'=>['required','integer','min:1'],'moloni_invoice_id'=>['nullable','integer','min:1']]);
        $item=$service->registerOrderFromPrestashop((int)$data['id_order'],isset($data['moloni_invoice_id'])?(int)$data['moloni_invoice_id']:null,'module');

        return response()->json(['success'=>true,'tracked'=>$item!==null,'data'=>$item?['id'=>$item->id,'status'=>$item->validation?->status]:null]);
    }

    public function orderStatus(Request $request, int $idOrder): JsonResponse
    {
        if(!$this->hasValidToken($request))return response()->json(['success'=>false,'message'=>'Invalid token'],403);
        $order=MoloniVatValidationOrder::query()->with('validation')->where('id_order',$idOrder)->first();
        if(!$order||!$order->validation)return response()->json(['success'=>true,'eligible'=>false,'alert'=>false]);

        $validation=$order->validation;
        $alert=!$validation->isFreshlyValid();

        return response()->json([
            'success'=>true,'eligible'=>true,'alert'=>$alert,'status'=>$validation->status,
            'message'=>$this->statusMessage($validation),'valid_until'=>$validation->valid_until?->toIso8601String(),
            'last_error'=>$validation->last_error,
        ]);
    }

    private function hasValidToken(Request $request): bool
    {
        $expected=(string)config('moloni_vat.token');
        $provided=(string)($request->bearerToken()?:$request->input('token'));
        return $expected!==''&&hash_equals($expected,$provided);
    }

    private function attachOrderMetadata($validations): void
    {
        $ids=$validations->flatMap(fn($v)=>$v->orders->pluck('id_order'))->unique()->values();
        if($ids->isEmpty())return;
        $prefix=env('DB2_DB_prefix','ps_');
        $orders=DB::connection('mysql2')->table($prefix.'orders as o')
            ->leftJoin($prefix.'customer as c','c.id_customer','=','o.id_customer')
            ->leftJoin($prefix.'address as a','a.id_address','=','o.id_address_invoice')
            ->whereIn('o.id_order',$ids)
            ->select(['o.id_order','o.reference','o.id_shop','c.firstname','c.lastname','a.company'])
            ->get()
            ->keyBy('id_order');

        foreach($validations as $validation){
            foreach($validation->orders as $link){
                $order=$orders->get((int)$link->id_order);
                $link->order_reference=$order?->reference;
                $link->customer_name=trim(($order?->firstname??'').' '.($order?->lastname??''));
                $link->company=trim((string)($order?->company??''));
                $link->store=(int)($order?->id_shop??0)===3?'ASD':'ASM';
                $link->prestashop_url=PrestashopAdminLinkService::dashboardOrderAdminUrl((int)$link->id_order,$link->store);
            }
        }
    }

    private function statuses(): array
    {
        return [
            MoloniVatValidation::STATUS_PENDING=>'Pendente',
            MoloniVatValidation::STATUS_PROCESSING=>'Em validação',
            MoloniVatValidation::STATUS_RETRY_SCHEDULED=>'Nova tentativa agendada',
            MoloniVatValidation::STATUS_VALID=>'Válido',
            MoloniVatValidation::STATUS_INVALID=>'Inválido',
            MoloniVatValidation::STATUS_MISSING_VAT=>'VAT/morada em falta',
            MoloniVatValidation::STATUS_MANUAL_REVIEW=>'Revisão manual',
        ];
    }

    private function statusMessage(MoloniVatValidation $validation): string
    {
        return match($validation->status) {
            MoloniVatValidation::STATUS_PENDING=>'VAT pendente de validação VIES.',
            MoloniVatValidation::STATUS_PROCESSING=>'VAT em validação VIES.',
            MoloniVatValidation::STATUS_RETRY_SCHEDULED=>'Não foi possível contactar VIES; nova tentativa agendada.',
            MoloniVatValidation::STATUS_INVALID=>'VAT inválido segundo VIES.',
            MoloniVatValidation::STATUS_MISSING_VAT=>'Falta VAT ou país na morada de faturação.',
            MoloniVatValidation::STATUS_MANUAL_REVIEW=>'VAT requer revisão manual.',
            default=>'Estado de VAT requer atenção.',
        };
    }
}
