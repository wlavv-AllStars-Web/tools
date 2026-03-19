<?php

namespace App\Models\modules\carrierIssues;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class carrierIssues extends Model
{
    use HasFactory;
    protected $table = "carrierIssues";
    public $primaryKey = 'id_issue';

    public static function getCarrierIssues($active = 1){
        
        if( $active == 1){
            return self::where('archived', 0)->orderBy('delay', 'DESC')->get();
        }elseif($active == 0){
            return self::where('archived', 1)->orderBy('delay', 'DESC')->get();
        }else{
            return self::where('archived', 2)->orderBy('delay', 'DESC')->get();
        }
    }
    
    public static function getIssue($id_issue){
        return carrierIssues::where('id_issue', $id_issue)->first();
    }
    
    
    public static function updateIssuesDelayDate(){

        $openIssues = self::where('archived', 0)->get();
        
        foreach( $openIssues AS $issue){
            $issue->delay = Carbon::parse($issue->claim_date)->diffInDays(Carbon::now());
            $issue->save(); 
        }
    }
    
    
    public static function saveData($form_data){
        
        $data = new carrierIssues();
        
        $data->shop = $form_data['shop'];
        $data->id_order = $form_data['id_order'];
        $data->tracking = $form_data['tracking'];
        $data->carrier = $form_data['carrier'];
        $data->country = $form_data['country'];
        $data->contact_date = $form_data['contact_date'];
        $data->issue = $form_data['issue'];
        $data->description = $form_data['description'];
        $data->claim_date = $form_data['claim_date'];
        $data->docs_sent = $form_data['docs_sent'];
        $data->amount_lost = $form_data['amount_lost'];
        $data->amount_claimed = $form_data['amount_claimed'];
        $data->ship_charges = $form_data['ship_charges'];
        $data->file_set = $form_data['file_set'];
        $data->claim_status = $form_data['claim_status'];
        $data->resolution = $form_data['resolution'];
        $data->new_tracking = $form_data['new_tracking'];
        $data->note = $form_data['note'];
        
        if(strlen($form_data['claim_date']) > 2){
            $data->delay = self::delayDays($form_data['claim_date']);
        }else{
            $data->delay = 0;
        }
        
        $data->save();
        
        return 1;
    }

    public static function updateData($form_data){
        
        $data = carrierIssues::where('id_issue', $form_data['id_issue'])->first();
        
        $data->shop = $form_data['shop'];
        $data->id_order = $form_data['id_order'];
        $data->tracking = $form_data['tracking'];
        $data->carrier = $form_data['carrier'];
        $data->country = $form_data['country'];
        $data->contact_date = $form_data['contact_date'];
        $data->issue = $form_data['issue'];
        $data->description = $form_data['description'];
        $data->claim_date = $form_data['claim_date'];
        $data->docs_sent = $form_data['docs_sent'];
        $data->amount_lost = $form_data['amount_lost'];
        $data->amount_claimed = $form_data['amount_claimed'];
        $data->ship_charges = $form_data['ship_charges'];
        $data->file_set = $form_data['file_set'];
        $data->claim_status = $form_data['claim_status'];
        $data->resolution = $form_data['resolution'];
        $data->new_tracking = $form_data['new_tracking'];
        $data->note = $form_data['note'];
        
        if(strlen($form_data['claim_date']) > 2){
            $data->delay = self::delayDays($form_data['claim_date']);
        }else{
            $data->delay = 0;
        }
        
        $data->save();
        
        return 1;
    }
    
    private static function delayDays($date){
        $startDate = Carbon::createFromFormat('Y-m-d', $date);
        return $startDate->diffInDays(Carbon::now());
    }
    
    public static function destroyRow($id_issue){
        carrierIssues::where('id_issue', $id_issue)->delete();
    }
    
    public static function archiveRow($id_issue, $status=1){
        $ready = carrierIssues::where('id_issue', $id_issue)->value('claim_status');
        
        //dd($ready);
        
        //if( $ready != 'PENDENTE' ){
            carrierIssues::where('id_issue', $id_issue)->update(['archived' => $status]);
        //}
        
        return $ready;
    }
    
}