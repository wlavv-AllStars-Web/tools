<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Config;
        
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class cart extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';
    public $timestamps = false;
    protected $primaryKey = 'id_cart';
    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB2_prefix') . 'cart';
    }
    
    public static function dashboard_dropcart_3_days($type){

        $data = array();

        $prefix = env('DB2_DB_prefix');

        $array = asm_dashboard::getExceptions('dropcart_3_days');
        
		$bd_data = DB::table(env('DB2_DB_prefix').'cart AS c')
			->join(env('DB2_DB_prefix').'customer AS cu', 'cu.id_customer', '=', 'c.id_customer')
			->join(env('DB2_DB_prefix').'cart_product AS cp', 'cp.id_cart', '=', 'c.id_cart')
			->join(env('DB2_DB_prefix').'product AS p', 'p.id_product', '=', 'cp.id_product')
			->where('c.status_sent', 0)
            ->whereBetween('c.date_add', [
                Carbon::now()->subDays(4)->startOfDay(),
                Carbon::now()->subDay()->endOfDay(), 
            ])
            ->whereNotIn('cu.id_customer', $array)
			->groupBy('c.id_cart', 'c.id_customer', 'cu.firstname', 'cu.lastname')
			->havingRaw('SUM(cp.quantity * p.price) > 0')
			->select(
				'c.id_cart',
				'c.date_add',
				'c.id_customer',
				DB::raw('CONCAT(cu.firstname, " ", cu.lastname) AS customer_name'),
				DB::raw('ROUND(SUM(cp.quantity * p.price), 2) AS cart_total')
			)
			->get();


        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_customer, 'id_cart' => $item->id_cart, 'id_customer' => $item->id_customer, 'customer_name' => $item->customer_name, 'date_add' => $item->date_add, 'cart_total' => $item->cart_total];
        
        return [
            'name'              => trans('dashboard.DROP CART 3 DAYS'),
            'col'               => 4,
            'item_id'           => $type . '_dropcart_3_days',
            'prestashop'        => ( isset ( Config::get('token')->AdminCustomers) ) ? [ 'token' => Config::get('token')->AdminCustomers, 'controller' => 'AdminCustomers', 'element' => 'id_customer', 'extraParameters' => '&viewcustomer' ] : [],
            'columns'           => ['clean', 'id_customer', 'customer_name', 'cart_total'],
            'counter'           => count($data),
            'exception_fields'  => ['dropcart_3_days', 'id_customer', 'customer_name', 'cart_total'],
            'data'              => $data
        ]; 
    }
    
    public static function dashboard_dropcart_7_days($type){

        $data = array();

        $prefix = env('DB2_DB_prefix');

        $array = asm_dashboard::getExceptions('dropcart_7_days');
        
		$bd_data = DB::table(env('DB2_DB_prefix').'cart AS c')
			->join(env('DB2_DB_prefix').'customer AS cu', 'cu.id_customer', '=', 'c.id_customer')
			->join(env('DB2_DB_prefix').'cart_product AS cp', 'cp.id_cart', '=', 'c.id_cart')
			->join(env('DB2_DB_prefix').'product AS p', 'p.id_product', '=', 'cp.id_product')
			->where('c.status_sent', 1)
            ->whereBetween('c.date_add', [
                Carbon::now()->subDays(10)->startOfDay(), // 29-09-2025 00:00
                Carbon::now()->subDays(7)->endOfDay(),    // 05-10-2025 23:59:59
            ])
			->whereNotIn('cu.id_customer', $array)
			->groupBy('c.id_cart', 'c.id_customer', 'cu.firstname', 'cu.lastname')
			->havingRaw('SUM(cp.quantity * p.price) > 0')
			->select(
				'c.id_cart',
				'c.date_add',
				'c.id_customer',
				DB::raw('CONCAT(cu.firstname, " ", cu.lastname) AS customer_name'),
				DB::raw('ROUND(SUM(cp.quantity * p.price), 2) AS cart_total')
			)
			->get();
			
        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_customer, 'id_cart' => $item->id_cart, 'id_customer' => $item->id_customer, 'customer_name' => $item->customer_name, 'date_add' => $item->date_add, 'cart_total' => $item->cart_total];
        
        return [
            'name'              => trans('dashboard.DROP CART 7 DAYS'),
            'col'               => 4,
            'item_id'           => $type . '_dropcart_7_days',
            'prestashop'        => ( isset ( Config::get('token')->AdminCustomers) ) ? [ 'token' => Config::get('token')->AdminCustomers, 'controller' => 'AdminCustomers', 'element' => 'id_customer', 'extraParameters' => '&viewcustomer' ] : [],
            'columns'           => ['clean', 'id_customer', 'customer_name', 'cart_total'],
            'counter'           => count($data),
            'exception_fields'  => ['dropcart_7_days', 'id_customer', 'customer_name', 'cart_total'],
            'data'              => $data
        ]; 
    }
    
    public static function dashboard_dropcart_phone($type){

        $data = array();

        $prefix = env('DB2_DB_prefix');

        $array = asm_dashboard::getExceptions('dropcart_phone');

		$bd_data = DB::table(env('DB2_DB_prefix').'cart AS c')
			->join(env('DB2_DB_prefix').'customer AS cu', 'cu.id_customer', '=', 'c.id_customer')
			->join(env('DB2_DB_prefix').'cart_product AS cp', 'cp.id_cart', '=', 'c.id_cart')
			->join(env('DB2_DB_prefix').'product AS p', 'p.id_product', '=', 'cp.id_product')
			->where('c.status_sent', 2)
            ->whereBetween('c.date_add', [
                Carbon::now()->subDays(20)->startOfDay(),
                Carbon::now()->subDays(10)->endOfDay(),
            ])
			->whereNotIn('cu.id_customer', $array)
			->groupBy('c.id_cart', 'c.id_customer', 'cu.firstname', 'cu.lastname')
			->havingRaw('SUM(cp.quantity * p.price) > 200')
			->select(
				'c.id_cart',
				'c.date_add',
				'c.id_customer',
				DB::raw('CONCAT(cu.firstname, " ", cu.lastname) AS customer_name'),
				DB::raw('ROUND(SUM(cp.quantity * p.price), 2) AS cart_total')
			)
			->get();


        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_customer, 'id_cart' => $item->id_cart, 'id_customer' => $item->id_customer, 'customer_name' => $item->customer_name, 'date_add' => $item->date_add, 'cart_total' => $item->cart_total];
        
        return [
            'name'              => trans('dashboard.DROP CART 3 DAYS'),
            'col'               => 4,
            'item_id'           => $type . '_dropcart_phone',
            'prestashop'        => ( isset ( Config::get('token')->AdminCustomers) ) ? [ 'token' => Config::get('token')->AdminCustomers, 'controller' => 'AdminCustomers', 'element' => 'id_customer', 'extraParameters' => '&viewcustomer' ] : [],
            'columns'           => ['clean', 'id_customer', 'customer_name', 'cart_total'],
            'counter'           => count($data),
            'exception_fields'  => ['dropcart_phone', 'id_customer', 'customer_name', 'cart_total'],
            'data'              => $data
        ]; 
    }
	
}
