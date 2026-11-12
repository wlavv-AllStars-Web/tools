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
        $data = self::getDashboardRows([1, 2], true, [], 'warranty');

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
        $data = self::getDashboardRows([3], false, [], 'warranty');

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

    /**
     * Separate dashboard panels for returns and warranties. Although both
     * processes share the order_return table, they must never be mixed in the
     * same operational queue.
     */
    public static function dashboard_returns($type)
    {
        return self::dashboardReturnWarrantyProcess($type, 'return');
    }

    public static function dashboard_warranties($type)
    {
        return self::dashboardReturnWarrantyProcess($type, 'warranty');
    }

    protected static function dashboardReturnWarrantyProcess($type, string $process): array
    {
        $closedReturnIds = asm_dashboard::getExceptions('closed_order_return')
            ->pluck('id_product')
            ->map(static fn ($id) => (int) $id)
            ->all();

        $closedWarrantyIds = asm_dashboard::getExceptions('closed_order_warranty')
            ->pluck('id_product')
            ->map(static fn ($id) => (int) $id)
            ->all();

        $data = self::getReturnsWarrantiesDashboardRows(
            $closedReturnIds,
            $closedWarrantyIds,
            $process
        );

        $isReturn = $process === 'return';

        return [
            'name' => $isReturn ? 'RETURNS' : 'WARRANTIES',
            'col' => 4,
            'item_id' => $type . '_' . ($isReturn ? 'returns' : 'warranties'),
            'columns' => ['clean', 'status', 'id_order', 'id_customer', 'customer_name'],
            'column_labels' => [
                'status' => 'Status',
                'id_order' => 'Order',
                'id_customer' => 'Customer ID',
                'customer_name' => 'Customer',
            ],
            'row_navigation' => 'replace',
            'counter' => count($data),
            'data' => $data,
        ];
    }

    protected static function getReturnsWarrantiesDashboardRows(
        array $closedReturnIds,
        array $closedWarrantyIds,
        string $process
    ): array
    {
        $orderReturnTable = self::tableName('order_return');
        $ordersTable = self::tableName('orders');
        $customerTable = self::tableName('customer');
        $stateLangTable = self::tableName('order_return_state_lang');

        $records = DB::connection('mysql2')
            ->table($orderReturnTable . ' as or')
            ->leftJoin($ordersTable . ' as o', 'o.id_order', '=', 'or.id_order')
            ->leftJoin($customerTable . ' as cu', 'cu.id_customer', '=', 'or.id_customer')
            ->leftJoin($customerTable . ' as ocu', 'ocu.id_customer', '=', 'o.id_customer')
            ->leftJoin($stateLangTable . ' as sl', function ($join) {
                $join->on('sl.id_order_return_state', '=', 'or.state')
                    ->where('sl.id_lang', '=', 2);
            })
            ->where('or.process', $process)
            ->whereIn('or.state', $process === 'return'
                ? [10, 11, 12, 13, 14]
                : [1, 2, 3, 4, 5, 6, 7, 8]
            )
            ->select(
                'or.id_order_return',
                'or.id_order',
                'or.process',
                'or.state',
                'sl.name as state_name',
                DB::raw('COALESCE(NULLIF(or.id_customer, 0), o.id_customer) AS id_customer'),
                DB::raw('TRIM(CONCAT(COALESCE(cu.firstname, ocu.firstname, ""), " ", COALESCE(cu.lastname, ocu.lastname, ""))) AS customer_name')
            )
            ->orderByDesc('or.id_order_return')
            ->get();

        $data = [];

        foreach ($records as $record) {
            $isClosedReturn = $record->process === 'return' && in_array((int) $record->state, [12, 13], true);
            $isClosedWarranty = $record->process === 'warranty' && in_array((int) $record->state, [4, 5, 6, 7, 8], true);

            if ($isClosedReturn && in_array((int) $record->id_order_return, $closedReturnIds, true)) {
                continue;
            }

            if ($isClosedWarranty && in_array((int) $record->id_order_return, $closedWarrantyIds, true)) {
                continue;
            }

            $isReturn = $record->process === 'return';
            $row = [
                'status' => self::returnWarrantyStatusLabel(
                    $record->process,
                    (int) $record->state,
                    $record->state_name
                ),
                'id_order' => $record->id_order,
                'id_customer' => $record->id_customer,
                'customer_name' => $record->customer_name,
                'url' => $isReturn
                    ? route('sales.tools.returns.index')
                    : route('sales.tools.warranties.index'),
            ];

            if ($isClosedReturn || $isClosedWarranty) {
                $row['clean'] = $record->id_order_return;
                $row['id_order_return'] = $record->id_order_return;
                $row['exception_fields'] = $isClosedReturn
                    ? ['closed_order_return', 'id_order_return', 'id_customer', 'customer_name']
                    : ['closed_order_warranty', 'id_order_return', 'id_customer', 'customer_name'];
            }

            $data[] = $row;
        }

        return $data;
    }

    protected static function returnWarrantyStatusLabel(string $process, int $state, ?string $stateName): string
    {
        if (!empty($stateName)) {
            return $stateName;
        }

        $labels = [
            'return' => [
                10 => 'Request registered',
                11 => 'Awaiting delivery',
                12 => 'Not approved',
                13 => 'Approved',
                14 => 'Package received',
            ],
            'warranty' => [
                1 => 'Request registered',
                2 => 'Request being processed',
                3 => 'Request for additional information',
                4 => 'Approved',
                5 => 'Not approved',
                6 => 'Closed',
                7 => 'Closed',
                8 => 'Closed',
            ],
        ];

        return $labels[$process][$state] ?? ('State ' . $state);
    }

    protected static function getDashboardRows(array $states, $includeClean = true, array $excludedReturnIds = [], ?string $process = null)
    {
        $orderReturnTable = self::tableName('order_return');
        $ordersTable = self::tableName('orders');
        $customerTable = self::tableName('customer');

        $query = DB::connection('mysql2')
            ->table($orderReturnTable . ' as or')
            ->leftJoin($ordersTable . ' as o', 'o.id_order', '=', 'or.id_order')
            ->leftJoin($customerTable . ' as cu', 'cu.id_customer', '=', 'or.id_customer')
            ->leftJoin($customerTable . ' as ocu', 'ocu.id_customer', '=', 'o.id_customer')
            ->whereIn('or.state', $states)
            ->select(
                'or.id_order_return',
                'or.id_order',
                DB::raw('COALESCE(NULLIF(or.id_customer, 0), o.id_customer) AS id_customer'),
                DB::raw('TRIM(CONCAT(COALESCE(cu.firstname, ocu.firstname, ""), " ", COALESCE(cu.lastname, ocu.lastname, ""))) AS customer_name')
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
