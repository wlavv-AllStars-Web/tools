<?php

namespace App\Models\modules\purchase_request;

use Illuminate\Database\Eloquent\Model;

class purchase_request extends Model
{
    protected $table = 'purchase_requests';

    protected $fillable = [
        'first_contact_date',
        'customer_contact',
        'language',
        'customer_message',

        'request',
        'link',
        'reference',
        'store',
        'sales_lead',

        'second_contact_date',
        'customer_response',

        'status',

        'supplier_price',
        'supplier_notes',
    ];

    protected $casts = [
        'first_contact_date'  => 'date',
        'second_contact_date' => 'date',
        'supplier_price'      => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'new',
        'language' => 'EN',
        'store' => 'ASM',
    ];

    public const STATUS_NEW = 'new';
    public const STATUS_WAITING_SUPPLIER = 'waiting_supplier';
    public const STATUS_QUOTED = 'quoted';
    public const STATUS_CLIENT_NOTIFIED = 'client_notified';
    public const STATUS_CLOSED = 'closed';

    public static function statuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_WAITING_SUPPLIER,
            self::STATUS_QUOTED,
            self::STATUS_CLIENT_NOTIFIED,
            self::STATUS_CLOSED,
        ];
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public static function dashboard_quote_backoffice($type){

        $data = array();

        $bd_data = self::select('store', 'reference', 'status')->whereIn('status', ['new', 'waiting_supplier'])->get();

        foreach($bd_data AS $item) $data[] = ['store' => $item->store, 'reference' => $item->reference, 'status' => strtoupper(str_replace('_', ' ', $item->status))];
        
        return [
            'name'              => "PRODUCT REQUEST'S ( Backoffice )",
            'col'               => 4,
            'item_id'           => $type . '_product_requests',
            'columns'           => ['store', 'reference', 'status'],
            'prestashop'        => null,
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_quote_frontoffice($type){

        $data = array();

        $bd_data = self::select('store', 'reference', 'status')->whereIn('status', ['quoted', 'client_notified'])->get();

        foreach($bd_data AS $item) $data[] = ['store' => $item->store, 'reference' => $item->reference, 'status' => strtoupper(str_replace('_', ' ', $item->status))];
        
        return [
            'name'              => "PRODUCT REQUEST'S ( Frontoffice )",
            'col'               => 4,
            'item_id'           => $type . '_product_requests',
            'columns'           => ['store', 'reference', 'status'],
            'prestashop'        => null,
            'counter'           => count($data),
            'data'              => $data
        ];        
    }
}
