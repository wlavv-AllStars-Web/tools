<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class order_return extends PrestashopModel
{
    use HasFactory;

    protected $primaryKey = 'id_order_return';
    protected $fillable = ['state'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('order_return');
    }

    public function status()
    {
        return $this->belongsTo(order_return_state::class, 'state', 'id_order_return_state');
    }

    public function customer()
    {
        return $this->hasOne(customer::class, 'id_customer', 'id_customer');
    }

    public function order()
    {
        return $this->hasOne(orders::class, 'id_order', 'id_order');
    }

    public function details()
    {
        return $this->hasMany(order_return_detail::class, 'id_order_return', 'id_order_return');
    }

    public static function dashboard_new_order_return($type)
    {
        $data = self::getDashboardRows([10], true, [], 'return');

        return [
            'name' => trans('ORDER RETURN - NEW'),
            'col' => 4,
            'item_id' => $type . '_new_order_return',
            'link' => route('returns.index', [0]),
            'prestashop' => null,
            'columns' => ['id_order', 'id_customer', 'customer_name'],
            'counter' => count($data),
            'data' => $data
        ];
    }

    public static function dashboard_received_order_return($type)
    {
        $data = self::getDashboardRows([14], true, [], 'return');

        return [
            'name' => trans('ORDER RETURN - PACKAGE RECEIVED'),
            'col' => 4,
            'item_id' => $type . '_received_order_return',
            'link' => route('returns.index', [0]),
            'prestashop' => null,
            'columns' => ['id_order', 'id_customer', 'customer_name'],
            'counter' => count($data),
            'data' => $data
        ];
    }

    public static function dashboard_progress_order_return($type)
    {
        $data = self::getDashboardRows([11], false, [], 'return');

        return [
            'name' => trans('ORDER RETURN - IN PROGRESS'),
            'col' => 4,
            'item_id' => $type . '_progress_order_return',
            'link' => route('returns.index', [0]),
            'prestashop' => null,
            'columns' => ['id_order', 'id_customer', 'customer_name'],
            'counter' => count($data),
            'data' => $data
        ];
    }

    public static function dashboard_closed_order_return($type)
    {
        $excludedReturnIds = asm_dashboard::getExceptions('closed_order_return')
            ->pluck('id_product')
            ->toArray();

        $data = self::getDashboardRows([12, 13], true, $excludedReturnIds, 'return');

        return [
            'name' => trans('ORDER RETURN - CLOSED'),
            'col' => 4,
            'item_id' => $type . '_closed_order_return',
            'link' => route('returns.index', [0]),
            'prestashop' => null,
            'columns' => ['clean', 'id_order', 'id_customer', 'customer_name'],
            'counter' => count($data),
            'exception_fields' => ['closed_order_return', 'id_order_return', 'id_customer', 'customer_name'],
            'data' => $data
        ];
    }

    public static function dashboard_new_order_warranty($type)
    {
        $data = self::getDashboardRows([1], true, [], 'warranty');

        return [
            'name' => trans('ORDER WARRANTY - NEW'),
            'col' => 4,
            'item_id' => $type . '_new_order_warranty',
            'link' => route('warranties.index', [0]),
            'columns' => ['id_order', 'id_customer', 'customer_name'],
            'counter' => count($data),
            'data' => $data
        ];
    }

    public static function dashboard_progress_order_warranty($type)
    {
        $data = self::getDashboardRows([1, 2], false, [], 'warranty');

        return [
            'name' => 'Warranty – Request for Additional Information',
            'col' => 4,
            'item_id' => $type . '_progress_order_warranty',
            'link' => route('warranties.index', [0]),
            'columns' => ['id_order', 'id_customer', 'customer_name'],
            'counter' => count($data),
            'data' => $data
        ];
    }

    public static function dashboard_closed_order_warranty($type)
    {
        $excludedReturnIds = asm_dashboard::getExceptions('closed_order_warranty')
            ->pluck('id_product')
            ->toArray();

        $data = self::getDashboardRows([4, 5, 6, 7, 8], true, $excludedReturnIds, 'warranty');

        return [
            'name' => trans('ORDER WARRANTY - CLOSED'),
            'col' => 4,
            'item_id' => $type . '_closed_order_warranty',
            'prestashop' => null,
            'columns' => ['clean', 'id_order', 'id_customer', 'customer_name'],
            'counter' => count($data),
            'exception_fields' => ['closed_order_warranty', 'id_order_return', 'id_customer', 'customer_name'],
            'data' => $data
        ];
    }

    protected static function getDashboardRows(array $states, $includeClean = true, array $excludedReturnIds = [], ?string $process = null)
    {
        $orderReturnTable = self::tableName('order_return');
        $customerTable = self::tableName('customer');

        $query = DB::connection('mysql2')
            ->table($orderReturnTable . ' as or')
            ->join($customerTable . ' as cu', 'cu.id_customer', '=', 'or.id_customer')
            ->whereIn('or.state', $states)
            ->select(
                'or.id_order_return',
                'or.id_order',
                'or.id_customer',
                DB::raw('CONCAT(cu.firstname, " ", cu.lastname) AS customer_name')
            );

        if ($process !== null) {
            $query->where('or.process', $process);
        }

        if (!empty($excludedReturnIds)) {
            $query->whereNotIn('or.id_order_return', $excludedReturnIds);
        }

        $bd_data = $query->get();

        $data = [];

        foreach ($bd_data as $item) {
            $row = [
                'id_order' => $item->id_order,
                'id_customer' => $item->id_customer,
                'customer_name' => $item->customer_name,
            ];

            if ($includeClean) {
                $row = array_merge([
                    'clean' => $item->id_order_return,
                    'id_order_return' => $item->id_order_return,
                ], $row);
            }

            $data[] = $row;
        }

        return $data;
    }
}
