<?php

namespace App\Models\modules\backorders_list;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\prestashop\suppliers;
use Illuminate\Support\Facades\DB;

class backorders_list extends Model
{
    protected $connection = 'mysql';
    use HasFactory;
    public $timestamps = false;

    public function __construct(){
        $this->table = env('DB_prefix')."backorders_list";
    }
    
    /** CHECK IF ALREADY EXISTS DATA INSERTED FOR GIVEN MONTH **/
    public static function checkIfExistData($report_month, $report_year){
        return self::where('report_month', $report_month)->where('report_year', $report_year)->count();
    }
    
    /** GET ALL BACKORDERS OF GIVEN YEAR AND MONTH **/
    public static function getBackordersOf($report_month, $report_year){
        return self::where('report_month', $report_month)->where('report_year', $report_year)->orderBy('supplier', 'ASC')->get();
    }
    
    /** GET A LIST OF SUPPLIERS WITH OPEN BACKORDERS FOR A GIVEN YEAR, MONTH **/
    public static function getSuppliersBackordersOf($report_month, $report_year){
        return self::select('id_supplier', 'supplier', 'token', 'reply', DB::raw('SUM(reply) AS quantity_replied'), DB::raw('count(reply) AS number_of_rows'))->where('report_month', $report_month)->where('report_year', $report_year)->groupBy('id_supplier')->orderBy('supplier', 'ASC')->get();
    }
    public static function getSupplierBackordersOf($id_supplier, $report_month, $report_year){
        return self::select('id_supplier', 'supplier', 'token', 'reply', DB::raw('SUM(reply) AS quantity_replied'), DB::raw('count(reply) AS number_of_rows'))->where('report_month', $report_month)->where('report_year', $report_year)->where('id_supplier', $id_supplier)->groupBy('id_supplier')->orderBy('supplier', 'ASC')->first();
    }
    
    /** GET ALL BACKORDERS OF GIVEN YEAR, MONTH AND SUPPLIER **/
    public static function getBackordersOfSupplier($id_supplier, $report_month, $report_year){
        
        $backorders = array();
        
        $by_month = self::select('order_month')->where('id_supplier', $id_supplier)->where('report_month', $report_month)->where('report_year', $report_year)->groupBy('order_month')->get();
        
        foreach($by_month AS $month){
            $by_order_reference = self::select('order_reference', 'order_id')->where('id_supplier', $id_supplier)->where('report_month', $report_month)->where('report_year', $report_year)->where('order_month', $month->order_month)->groupBy('order_reference')->get();
            foreach($by_order_reference AS $order_reference){
                $rows = self::where('id_supplier', $id_supplier)->where('report_month', $report_month)->where('report_year', $report_year)->where('order_month', $month->order_month)->where('order_reference', $order_reference->order_reference)->get();
                $backorders[$month->order_month][$order_reference->order_id]= $rows;
            }
        }
        
        return $backorders;
    }

    public static function getFirstSupplierBackordersOf($report_month, $report_year){
        return self::select('id_supplier', 'supplier')->where('report_month', $report_month)->where('report_year', $report_year)->groupBy('id_supplier')->orderBy('supplier', 'ASC')->first();
    }
    
    public static function getFirstSupplierBackordersFromTokenOf($id_supplier, $token){
        return self::select('id_supplier', 'supplier', 'token', 'reply')->where('id_supplier', $id_supplier)->where('token', $token)->first();
    }
    
    
    public static function getSupplierRepliedFromTokenOf($id_supplier, $token){
        $rows = self::where('id_supplier', $id_supplier)->where('token', $token)->count();
        $replied = self::where('id_supplier', $id_supplier)->where('reply', '1')->where('token', $token)->count();
        
        return ($rows == $replied) ? 1 : 0;
    }
    
    public static function getBackordersOfSupplierFromToken($id_supplier, $token){
        
        $backorders = array();
        
        $by_month = self::select('order_month')->where('id_supplier', $id_supplier)->where('token', $token)->groupBy('order_month')->get();
        
        foreach($by_month AS $month){
            $by_order_reference = self::select('order_reference', 'order_id')->where('id_supplier', $id_supplier)->where('token', $token)->where('order_month', $month->order_month)->groupBy('order_reference')->get();
            foreach($by_order_reference AS $order_reference){
                $rows = self::where('id_supplier', $id_supplier)->where('token', $token)->where('order_month', $month->order_month)->where('order_reference', $order_reference->order_reference)->get();
                $backorders[$month->order_month][$order_reference->order_id]= $rows;
            }
        }
        
        return $backorders;
    }
        
    public static function saveBackordersReport($data){
        
        if( $data['qty_billed'] != $data['qty_ordered'] ){
            
            $report = new backorders_list();
            $report->id_supplier = $data['id_supplier'];
            $report->supplier = $data['supplier'];
            $report->report_month = $data['report_month'];
            $report->report_year = $data['report_year']; 
            $report->order_id = $data['order_id'];
            $report->order_reference = $data['order_reference'];
            $report->order_date = $data['order_date'];
            $report->order_month = $data['order_month'];
            $report->product_reference = $data['product_reference'];
            $report->qty_ordered = $data['qty_ordered'];
            $report->qty_billed = $data['qty_billed'];
            $report->qty_received = $data['qty_received'];
            $report->supplier_comment = '';
            $report->sent = 0;
            $report->download = 0;
            $report->reply = 0;
            $report->reply_date = null;
            $report->token = $data['token'];
            $report->save();
        }
                
    }
}
