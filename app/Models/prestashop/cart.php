<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class cart extends PrestashopModel
{
    use HasFactory;

    protected $primaryKey = 'id_cart';
    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('cart');
    }

    public static function dashboard_dropcart_3_days($type)
    {
        $data = self::getDropcartData(
            'dropcart_3_days',
            0,
            Carbon::now()->subDays(4)->startOfDay(),
            Carbon::now()->subDay()->endOfDay(),
            0
        );

        return [
            'name' => trans('dashboard.DROP CART 3 DAYS'),
            'col' => 4,
            'item_id' => $type . '_dropcart_3_days',
            'prestashop' => self::adminCustomerLink('ASM'),
            'columns' => ['clean', 'id_customer', 'customer_name', 'cart_total'],
            'counter' => count($data),
            'exception_fields' => ['dropcart_3_days', 'id_customer', 'customer_name', 'cart_total'],
            'data' => $data
        ];
    }

    public static function dashboard_dropcart_7_days($type)
    {
        $data = self::getDropcartData(
            'dropcart_7_days',
            1,
            Carbon::now()->subDays(10)->startOfDay(),
            Carbon::now()->subDays(7)->endOfDay(),
            0
        );

        return [
            'name' => trans('dashboard.DROP CART 7 DAYS'),
            'col' => 4,
            'item_id' => $type . '_dropcart_7_days',
            'prestashop' => self::adminCustomerLink('ASM'),
            'columns' => ['clean', 'id_customer', 'customer_name', 'cart_total'],
            'counter' => count($data),
            'exception_fields' => ['dropcart_7_days', 'id_customer', 'customer_name', 'cart_total'],
            'data' => $data
        ];
    }

    public static function dashboard_dropcart_phone($type)
    {
        $data = self::getDropcartData(
            'dropcart_phone',
            2,
            Carbon::now()->subDays(20)->startOfDay(),
            Carbon::now()->subDays(10)->endOfDay(),
            200
        );

        return [
            'name' => trans('dashboard.DROP CART 3 DAYS'),
            'col' => 4,
            'item_id' => $type . '_dropcart_phone',
            'prestashop' => self::adminCustomerLink('ASM'),
            'columns' => ['clean', 'id_customer', 'customer_name', 'cart_total'],
            'counter' => count($data),
            'exception_fields' => ['dropcart_phone', 'id_customer', 'customer_name', 'cart_total'],
            'data' => $data
        ];
    }

    protected static function getDropcartData($board, $statusSent, $dateFrom, $dateTo, $minTotal = 0)
    {
        $data = [];

        $cartTable = self::tableName('cart');
        $customerTable = self::tableName('customer');
        $cartProductTable = self::tableName('cart_product');
        $productTable = self::tableName('product');

        if (!self::hasPrestashopColumn($cartTable, 'status_sent')) {
            return $data;
        }

        $exceptions = asm_dashboard::getExceptions($board)
            ->pluck('id_product')
            ->toArray();

        $query = DB::connection('mysql2')
            ->table($cartTable . ' as c')
            ->join($customerTable . ' as cu', 'cu.id_customer', '=', 'c.id_customer')
            ->join($cartProductTable . ' as cp', 'cp.id_cart', '=', 'c.id_cart')
            ->join($productTable . ' as p', 'p.id_product', '=', 'cp.id_product')
            ->where('c.status_sent', $statusSent)
            ->whereBetween('c.date_add', [$dateFrom, $dateTo])
            ->groupBy('c.id_cart', 'c.id_customer', 'cu.firstname', 'cu.lastname', 'c.date_add')
            ->havingRaw('SUM(cp.quantity * p.price) > ?', [$minTotal])
            ->select(
                'c.id_cart',
                'c.date_add',
                'c.id_customer',
                DB::raw('CONCAT(cu.firstname, " ", cu.lastname) AS customer_name'),
                DB::raw('ROUND(SUM(cp.quantity * p.price), 2) AS cart_total')
            );

        if (!empty($exceptions)) {
            $query->whereNotIn('cu.id_customer', $exceptions);
        }

        $bd_data = $query->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'clean' => $item->id_customer,
                'id_cart' => $item->id_cart,
                'id_customer' => $item->id_customer,
                'customer_name' => $item->customer_name,
                'date_add' => $item->date_add,
                'cart_total' => $item->cart_total
            ];
        }

        return $data;
    }
}
