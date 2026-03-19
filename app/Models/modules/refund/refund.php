<?php

namespace App\Models\modules\refund;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class refund extends Model
{
    use HasFactory;
    protected $table = "refunds";

public static function getRefunds($year = null, $month = null, $method = null, $refunds = 'active')
{
    $refund_query = refund::query()
        ->select('*', env('DB2_DB_prefix') . 'orders.date_add AS purchase_date')
        ->leftjoin(env('DB2_DB_prefix') . 'orders', 'refunds.id_order', '=', env('DB2_DB_prefix') . 'orders.id_order')
        ->leftjoin(env('DB2_DB_prefix') . 'customer', env('DB2_DB_prefix') . 'orders.id_customer', '=', env('DB2_DB_prefix') . 'customer.id_customer')
        ->leftjoin(env('DB2_DB_prefix') . 'order_state_lang', env('DB2_DB_prefix') . 'orders.current_state', '=', env('DB2_DB_prefix') . 'order_state_lang.id_order_state')
        ->groupBy('refunds.id')
        ->where('refunds.id', '>', 0);

    if (!is_null($method)) {
        $refund_query->where('refunds.refund_payment_method', $method);
    }

    if (!is_null($year) && !is_null($month)) {
        $refund_query->whereYear('refunds.refund_date', $year)
                     ->whereMonth('refunds.refund_date', $month);
    } elseif (!is_null($year)) {
        $refund_query->whereYear('refunds.refund_date', $year);
    } elseif (!is_null($month)) {
        $currentYear = date('Y');
        $refund_query->whereYear('refunds.refund_date', $currentYear)
                     ->whereMonth('refunds.refund_date', $month);
    }
    
    if( $refunds == 'active'){
        $refund_query->whereIn('refund_status', ['Pending']);
    }else{
        $refund_query->whereNotIn('refund_status', ['Pending']);
    }
    
    $refund_query->groupBy('refunds.id');
    
    return $refund_query->get();
}



    public static function newRefund($data){
        
        $newRefund = new refund();
        $newRefund->id_order = $data->order_id;
        $newRefund->country = $data->country;
        $newRefund->lang = $data->lang;
        $newRefund->order_modification = $data->order_modification;
        $newRefund->return_file_ok = $data->return_file_ok;
        $newRefund->product_reference = $data->product_reference;
        $newRefund->refund_reason = $data->refund_reason;
        $newRefund->refund_payment_method = $data->refund_payment_method;
        $newRefund->amount_to_refund = $data->amount_to_refund;
        $newRefund->amount_refunded = $data->amount_refunded;
        $newRefund->refund_date = $data->refund_date;
        $newRefund->credit_note = $data->credit_note;
        $newRefund->refund_status = $data->refund_status;
        $newRefund->new_order_id = $data->new_order_id;
        $newRefund->new_order_amount = $data->new_order_amount;
        $newRefund->eta = $data->eta;
        $newRefund->save();
        
        return 1;
    }
    
    public static function updateRefund($id, $data){
        
        $refund = refund::findOrFail($id);
    
        $refund->id_order = $data->order_id;
        $refund->country = $data->country;
        $refund->lang = $data->lang;
        $refund->order_modification = $data->order_modification;
        $refund->return_file_ok = $data->return_file_ok;
        $refund->product_reference = $data->product_reference;
        $refund->refund_reason = $data->refund_reason;
        $refund->refund_payment_method = $data->refund_payment_method;
        $refund->amount_to_refund = $data->amount_to_refund;
        $refund->amount_refunded = $data->amount_refunded;
        $refund->refund_date = $data->refund_date;
        $refund->credit_note = $data->credit_note;
        $refund->refund_status = $data->refund_status;
        $refund->new_order_id = $data->new_order_id;
        $refund->new_order_amount = $data->new_order_amount;
        $refund->eta = $data->eta;
    
        $refund->save();
    
        return 1;
    }

        

}