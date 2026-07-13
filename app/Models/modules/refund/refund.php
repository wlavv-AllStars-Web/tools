<?php

namespace App\Models\modules\refund;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\prestashop\orders;
use App\Models\prestashop\customer;
use App\Models\prestashop\order_state_lang;

class refund extends Model
{
    use HasFactory;
    protected $table = "refunds";

public static function getRefunds($year = null, $month = null, $method = null, $refunds = 'active')
{
    $refund_query = self::query()
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
    
    if ($refunds == 'active') {
        $refund_query->whereIn('refund_status', ['Pending']);
    } else {
        $refund_query->whereNotIn('refund_status', ['Pending']);
    }
    
    return self::enrichPrestashopData($refund_query->orderBy('refunds.id', 'DESC')->get());
}

public static function enrichPrestashopData($refunds)
{
    $single = $refunds instanceof self;
    $collection = $single ? collect([$refunds]) : collect($refunds);

    $orderIds = $collection
        ->pluck('id_order')
        ->filter()
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values();

    if ($orderIds->isEmpty()) {
        return $single ? $refunds : $refunds;
    }

    $orders = orders::whereIn('id_order', $orderIds)->get()->keyBy('id_order');

    $customerIds = $orders
        ->pluck('id_customer')
        ->filter()
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values();

    $customers = $customerIds->isEmpty()
        ? collect()
        : customer::whereIn('id_customer', $customerIds)->get()->keyBy('id_customer');

    $stateIds = $orders
        ->pluck('current_state')
        ->filter()
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values();

    $states = $stateIds->isEmpty()
        ? collect()
        : order_state_lang::whereIn('id_order_state', $stateIds)
            ->where('id_lang', 1)
            ->get()
            ->keyBy('id_order_state');

    foreach ($collection as $refund) {
        $order = $orders->get((int) $refund->id_order);

        $refund->purchase_date = $order?->date_add;
        $refund->id_customer = $order?->id_customer;
        $refund->total_paid = $order?->total_paid;
        $refund->order_total = $order?->total_paid;
        $refund->order_id = $refund->id_order;

        $customer = $order ? $customers->get((int) $order->id_customer) : null;
        $refund->email = $customer?->email;
        $refund->client_email = $customer?->email;
        $refund->firstname = $customer?->firstname;
        $refund->lastname = $customer?->lastname;
        $refund->client_name = trim(($customer?->firstname ?? '') . ' ' . ($customer?->lastname ?? ''));

        $state = $order ? $states->get((int) $order->current_state) : null;
        $refund->name = $state?->name;
    }

    return $single ? $refunds : $refunds;
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
