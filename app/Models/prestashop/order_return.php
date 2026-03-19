<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Config;
        
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\prestashop\orders;
use App\Models\prestashop\customer;
use App\Models\prestashop\order_return_detail;
use App\Models\prestashop\order_return_state;

class order_return extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';
    public $timestamps = false;
    protected $primaryKey = 'id_order_return';
    protected $fillable = ['state'];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = env('DB2_prefix') . 'order_return';
    }

    public function status(){
        return $this->belongsTo(order_return_state::class, 'state');
    }
    

    public function customer(){
        return $this->hasOne(customer::class, "id_customer", 'id_customer');
    }

    public function order(){
        return $this->hasOne(orders::class, "id_order", 'id_order');
    }

    public function details(){
        return $this->hasMany(order_return_detail::class, 'id_order_return');
    }

    public static function dashboard_new_order_return($type){

        $data = array();

		$bd_data = DB::table(env('DB2_DB_prefix').'order_return AS or')
			->join(env('DB2_DB_prefix').'customer AS cu', 'cu.id_customer', '=', 'or.id_customer')
			->where('state', 10)
    		->select(
				'or.id_order_return',
				'or.id_order',
				'or.id_customer',
				DB::raw('CONCAT(cu.firstname, " ", cu.lastname) AS customer_name')
			)
			->get();


        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_order_return, 'id_order' => $item->id_order, 'id_customer' => $item->id_customer, 'customer_name' => $item->customer_name];
        
        return [
            'name'              => trans('ORDER RETURN - NEW'),
            'col'               => 4,
            'item_id'           => $type . '_new_order_return',
            'link'              => route('returns.index', [0]),
            'prestashop'        => null,
            /**'prestashop'        => ( isset ( Config::get('token')->AdminCustomers) ) ? [ 'token' => Config::get('token')->AdminCustomers, 'controller' => 'AdminCustomers', 'element' => 'id_customer', 'extraParameters' => '&viewcustomer' ] : [],**/
            'columns'           => ['id_order', 'id_customer', 'customer_name'],
            'counter'           => count($data),
            'data'              => $data
        ]; 
    }

    public static function dashboard_received_order_return($type){

        $data = array();

		$bd_data = DB::table(env('DB2_DB_prefix').'order_return AS or')
			->join(env('DB2_DB_prefix').'customer AS cu', 'cu.id_customer', '=', 'or.id_customer')
			->where('state', 14)
    		->select(
				'or.id_order_return',
				'or.id_order',
				'or.id_customer',
				DB::raw('CONCAT(cu.firstname, " ", cu.lastname) AS customer_name')
			)
			->get();


        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_order_return, 'id_order' => $item->id_order, 'id_customer' => $item->id_customer, 'customer_name' => $item->customer_name];
        
        return [
            'name'              => trans('ORDER RETURN - PACKAGE RECEIVED'),
            'col'               => 4,
            'item_id'           => $type . '_new_order_return',
            'link'              => route('returns.index', [0]),
            'prestashop'        => null,
            /**'prestashop'        => ( isset ( Config::get('token')->AdminCustomers) ) ? [ 'token' => Config::get('token')->AdminCustomers, 'controller' => 'AdminCustomers', 'element' => 'id_customer', 'extraParameters' => '&viewcustomer' ] : [],**/
            'columns'           => ['id_order', 'id_customer', 'customer_name'],
            'counter'           => count($data),
            'data'              => $data
        ]; 
    }
    
    public static function dashboard_progress_order_return($type){

        $data = array();

		$bd_data = DB::table(env('DB2_DB_prefix').'order_return AS or')
			->join(env('DB2_DB_prefix').'customer AS cu', 'cu.id_customer', '=', 'or.id_customer')
			->whereIn('state', [11])
    		->select(
				'or.id_order_return',
				'or.id_order',
				'or.id_customer',
				DB::raw('CONCAT(cu.firstname, " ", cu.lastname) AS customer_name')
			)
			->get();


        foreach($bd_data AS $item) $data[] = ['id_order' => $item->id_order, 'id_customer' => $item->id_customer, 'customer_name' => $item->customer_name];
        
        return [
            'name'              => trans('ORDER RETURN - IN PROGRESS'),
            'col'               => 4,
            'item_id'           => $type . '_progress_order_return',
            'link'              => route('returns.index', [0]),
            'prestashop'        => null,
            /**'prestashop'        => ( isset ( Config::get('token')->AdminCustomers) ) ? [ 'token' => Config::get('token')->AdminCustomers, 'controller' => 'AdminCustomers', 'element' => 'id_customer', 'extraParameters' => '&viewcustomer' ] : [],**/
            'columns'           => ['id_order', 'id_customer', 'customer_name'],
            'counter'           => count($data),
            'data'              => $data
        ]; 
    }
    
    public static function dashboard_closed_order_return($type){

        $data = array();

        $prefix = env('DB2_DB_prefix');

        $array = asm_dashboard::getExceptions('closed_order_return');

		$bd_data = DB::table(env('DB2_DB_prefix').'order_return AS or')
			->join(env('DB2_DB_prefix').'customer AS cu', 'cu.id_customer', '=', 'or.id_customer')
			->whereIn('state', [12, 13])
			->whereNotIn(env('DB2_DB_prefix').'order_return.id_order_return', $array)
    		->select(
				'or.id_order_return',
				'or.id_order',
				'or.id_customer',
				DB::raw('CONCAT(cu.firstname, " ", cu.lastname) AS customer_name')
			)
			->get();


        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_order_return, 'id_order_return' => $item->id_order_return, 'id_order' => $item->id_order, 'id_customer' => $item->id_customer, 'customer_name' => $item->customer_name];
        
        return [
            'name'              => trans('ORDER RETURN - CLOSED'),
            'col'               => 4,
            'item_id'           => $type . '_closed_order_return',
            'link'              => route('returns.index', [0]),
            'prestashop'        => null,
            /**'prestashop'        => ( isset ( Config::get('token')->AdminCustomers) ) ? [ 'token' => Config::get('token')->AdminCustomers, 'controller' => 'AdminCustomers', 'element' => 'id_customer', 'extraParameters' => '&viewcustomer' ] : [],**/
            'columns'           => ['clean', 'id_order', 'id_customer', 'customer_name'],
            'counter'           => count($data),
            'exception_fields'  => ['closed_order_return', 'id_order_return', 'id_customer', 'customer_name'],
            'data'              => $data
        ]; 
    }
    
    public static function dashboard_new_order_warranty($type){

        $data = array();

		$bd_data = DB::table(env('DB2_DB_prefix').'order_return AS or')
			->join(env('DB2_DB_prefix').'customer AS cu', 'cu.id_customer', '=', 'or.id_customer')
			->where('state', 2)
    		->select(
				'or.id_order_return',
				'or.id_order',
				'or.id_customer',
				DB::raw('CONCAT(cu.firstname, " ", cu.lastname) AS customer_name')
			)
			->get();


        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_order_return, 'id_order' => $item->id_order, 'id_customer' => $item->id_customer, 'customer_name' => $item->customer_name];
        
        return [
            'name'              => trans('ORDER WARRANTY - NEW'),
            'col'               => 4,
            'item_id'           => $type . '_new_order_warranty',
            'link'              => route('warranties.index', [0]),
            /**'prestashop'        => ( isset ( Config::get('token')->AdminCustomers) ) ? [ 'token' => Config::get('token')->AdminCustomers, 'controller' => 'AdminCustomers', 'element' => 'id_customer', 'extraParameters' => '&viewcustomer' ] : [],**/
            'columns'           => ['id_order', 'id_customer', 'customer_name'],
            'counter'           => count($data),
            'data'              => $data
        ]; 
    }
    
    public static function dashboard_progress_order_warranty($type){

        $data = array();

		$bd_data = DB::table(env('DB2_DB_prefix').'order_return AS or')
			->join(env('DB2_DB_prefix').'customer AS cu', 'cu.id_customer', '=', 'or.id_customer')
			->whereIn('state', [3])
    		->select(
				'or.id_order_return',
				'or.id_order',
				'or.id_customer',
				DB::raw('CONCAT(cu.firstname, " ", cu.lastname) AS customer_name')
			)
			->get();


        foreach($bd_data AS $item) $data[] = ['id_order' => $item->id_order, 'id_customer' => $item->id_customer, 'customer_name' => $item->customer_name];
        
        return [
            'name'              => 'Warranty – Request for Additional Information',
            'col'               => 4,
            'item_id'           => $type . '_progress_order_warranty',
            'link'              => route('warranties.index', [0]),
            /**'prestashop'        => ( isset ( Config::get('token')->AdminCustomers) ) ? [ 'token' => Config::get('token')->AdminCustomers, 'controller' => 'AdminCustomers', 'element' => 'id_customer', 'extraParameters' => '&viewcustomer' ] : [],**/
            'columns'           => ['id_order', 'id_customer', 'customer_name'],
            'counter'           => count($data),
            'data'              => $data
        ]; 
    }
    
    public static function dashboard_closed_order_warranty($type){

        $data = array();

        $array = asm_dashboard::getExceptions('closed_order_warranty');

		$bd_data = DB::table(env('DB2_DB_prefix').'order_return AS or')
			->join(env('DB2_DB_prefix').'customer AS cu', 'cu.id_customer', '=', 'or.id_customer')
			->whereIn('state', [3, 8])
			->whereNotIn('or.id_order_return', $array)
    		->select(
				'or.id_order_return',
				'or.id_order',
				'or.id_customer',
				DB::raw('CONCAT(cu.firstname, " ", cu.lastname) AS customer_name')
			)
			->get();


        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_order_return, 'id_order_return' => $item->id_order_return, 'id_order' => $item->id_order, 'id_customer' => $item->id_customer, 'customer_name' => $item->customer_name];
        
        return [
            'name'              => trans('ORDER WARRANTY - CLOSED'),
            'col'               => 4,
            'item_id'           => $type . '_closed_order_warranty',
            'prestashop'        => ( isset ( Config::get('token')->AdminCustomers) ) ? [ 'token' => Config::get('token')->AdminCustomers, 'controller' => 'AdminCustomers', 'element' => 'id_customer', 'extraParameters' => '&viewcustomer' ] : [],
            'columns'           => ['clean', 'id_order', 'id_customer', 'customer_name'],
            'counter'           => count($data),
            'exception_fields'  => ['closed_order_warranty', 'id_order_return', 'id_customer', 'customer_name'],
            'data'              => $data
        ]; 
    }
    
}
