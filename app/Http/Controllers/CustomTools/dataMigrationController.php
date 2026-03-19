<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DataMigrationController extends Controller
{
    
    public function compareTableIDsASD()
    {

        $oldConnection = 'mysql4';
        $newConnection = 'mysql3';

        $lastInsertedIds = [];

        $allowed_tables = [

            // FASE 1
            /**
            'psnz_address',
            'psnz_customer',
            'psnz_customer_group',
            'psnz_mail',
            'psnz_message',
            **/
            
            //FASE 2 - PARA EM 149
            /**
            'psnz_order_carrier',
            **/
            
            //FASE 3 - PRIMEIROS 20000
            /**
            'psnz_order_detail',
            **/
            
            //FASE 4 - SEGUNDOS 20000
            /**
            'psnz_order_detail',
            **/
            
            //FASE 5 - ULTIMOS 20000
            /**
            'psnz_order_detail',
            **/
            
            //FASE 5 - ULTIMOS 20000
            /*
            'psnz_order_detail_tax',
            'psnz_order_history',
            */

            //FASE 6
            /**
            'psnz_order_invoice',
            'psnz_order_invoice_payment',
            'psnz_order_payment',
            'psnz_orders',
            **/
        ];

        try {
            $oldTables = DB::connection($oldConnection)->select("SHOW TABLES");
            $oldTableNames = array_map('current', json_decode(json_encode($oldTables), true));
        } catch (\Exception $e) {
            \Log::error('Erro ao recuperar tabelas da base antiga: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao acessar a base de dados antiga.']);
        }
        
        $id_customer = 65000;
        $id_mail     = 500000;
        $id_address  = 75000;
        
        $id_order    = 100000;
        $id_order_carrier= 100000;
        $id_order_invoice= 100000;
        $id_order_detail = 175000;
        $id_order_history = 360000;
        $id_order_payment = 100000;
        
        foreach ($oldTableNames as $table) {
            if( in_array($table, $allowed_tables) ){
            
                $k_id_shop = null;
                $k_id_lang = null;
                $insert_queries = array();

                $columns = DB::connection($oldConnection)->select("SHOW COLUMNS FROM `$table`");
                
                $idColumn = $columns[0]->Field; // Assume-se que a primeira coluna é o ID
    
                $lastOldId = DB::connection($oldConnection)->table($table)->orderBy($idColumn, 'desc')->value($idColumn);

                $newTables = DB::connection($oldConnection)->select("SHOW TABLES");
                $newTableNames = array_map('current', json_decode(json_encode($newTables), true));
                
                $inserts_data = DB::connection($oldConnection)->table($table)->where($idColumn, '>', 0)->orderBy($idColumn, 'desc')->get();
                
                /**
                $inserts_data = DB::connection($oldConnection)->table($table)->where($idColumn, '<', 20001)->orderBy($idColumn, 'desc')->get();
                $inserts_data = DB::connection($oldConnection)->table($table)->where($idColumn, '>', 20000)->where($idColumn, '<', 40001)->orderBy($idColumn, 'desc')->get();
                $inserts_data = DB::connection($oldConnection)->table($table)->where($idColumn, '>', 40000)->orderBy($idColumn, 'desc')->get();
                **/
                
                $lastNewId = DB::connection($oldConnection)->table($table)->orderBy($idColumn, 'desc')->value($idColumn);
                
                foreach($inserts_data AS $new){
                    
                    $columns_array = array();
                    $values_array = array();
                    
                    foreach($columns AS $key => $column){
                        
                        if( $column->Field == 'id_shop' ) $k_id_shop = $key;
                        if( $column->Field == 'id_lang' ) $k_id_lang = $key;
                        
                        $columns_array[] = addslashes($column->Field);

                    }
                    
                    foreach($columns_array AS $field_k => $column){
                        
                        if( (($column == 'cover') || ($column == 'default_on') || ($column == 'stock_arrivepa') || ($column == 'fecha_baja') ) && ( is_null($new->$column))){
                            $values_array[$field_k] = "NULL";
                        }elseif($column == 'id_customer'){
                            $values_array[$field_k] = "'" . addslashes( $id_customer + $new->$column) . "'";
                        }elseif($column == 'id_mail'){
                            $values_array[$field_k] = "'" . addslashes( $id_mail + $new->$column) . "'";
                        }elseif($column == 'id_address'){
                            $values_array[$field_k] = "'" . addslashes( $id_address + $new->$column) . "'";
                        }elseif($column == 'id_address_delivery'){
                            $values_array[$field_k] = "'" . addslashes( $id_address + $new->$column) . "'";
                        }elseif($column == 'id_address_invoice'){
                            $values_array[$field_k] = "'" . addslashes( $id_address + $new->$column) . "'";
                        }elseif($column == 'id_order'){
                            $values_array[$field_k] = "'" . addslashes( $id_order + $new->$column) . "'";
                        }elseif($column == 'id_order_detail'){
                            $values_array[$field_k] = "'" . addslashes( $id_order_detail + $new->$column) . "'";
                        }elseif($column == 'id_order_carrier'){
                            $values_array[$field_k] = "'" . addslashes( $id_order_carrier + $new->$column) . "'";
                        }elseif($column == 'id_order_invoice'){
                            $values_array[$field_k] = "'" . addslashes( $id_order_invoice + $new->$column) . "'";
                        }elseif($column == 'id_order_payment'){
                            $values_array[$field_k] = "'" . addslashes( $id_order_payment + $new->$column) . "'";
                        }elseif($column == 'id_order_history'){
                            $values_array[$field_k] = "'" . addslashes( $id_order_history + $new->$column) . "'";
                        }elseif($column == 'id_default_group'){
                            $values_array[$field_k] = "'" . addslashes( 5 ) . "'";
                        }else{
                            $values_array[$field_k] = "'" . addslashes($new->$column) . "'";
                        }
                    }
                    
                    if( (!is_null($k_id_shop)) && ( $values_array[$k_id_shop] == "'" . 1 . "'" ) ) $values_array[$k_id_shop] = "'" . 3 . "'";
                    
                    if( (!is_null($k_id_lang)) && ( $values_array[$k_id_lang] == "'1'" ) ) $values_array[$k_id_lang] = 2;
                    if( (!is_null($k_id_lang)) && ( $values_array[$k_id_lang] == "'2'" ) ) $values_array[$k_id_lang] = 4;
                    if( (!is_null($k_id_lang)) && ( $values_array[$k_id_lang] == "'3'" ) ) $values_array[$k_id_lang] = 5;
                    if( (!is_null($k_id_lang)) && ( $values_array[$k_id_lang] == "'4'" ) ) $values_array[$k_id_lang] = 1;
                    if( (!is_null($k_id_lang)) && ( $values_array[$k_id_lang] == "'6'" ) ) $values_array[$k_id_lang] = 7;
                    
                    if( $table == 'psnz_customer_group'  ) $values_array[1] = 5;
                    
                    echo "<br><br>INSERT INTO `$table` (" . implode(', ', $columns_array) . ") VALUES (" . implode(', ', $values_array) . ");";
                }
                
                if(count($insert_queries) > 0){
                    $lastInsertedIds[$table] = [
                        'id_column' => $idColumn,
                        'last_old_id' => $lastOldId ?? 'Sem registos',
                        'last_new_id' => $lastNewId ?? 'Sem registos',
                        'insert_queries' => $insert_queries
                    ];
                }
            }
        }
        
        echo '<br><br>DONE!';
        exit;
        
        return view('compare_last_ids', compact('lastInsertedIds'));
        
    }
    
    
    
    
    
    
    
    public function showMappingPage($table){

        $oldDatabaseName = env('DB2_DATABASE');
        $newDatabaseName = env('DB3_DATABASE');
        
        $oldTable = DB::connection('mysql2')->select("SHOW TABLES");
        $newTable = DB::connection('mysql3')->select("SHOW TABLES");

        $oldTables = array_map('current', json_decode(json_encode($oldTable), true));
        $newTables = array_map('current', json_decode(json_encode($newTable), true));

        $oldColumns = [];
        $newColumns = [];
        
        $oldColumns_connection = DB::connection('mysql2')->select("SHOW COLUMNS FROM " . $table);
        $newColumns_connection = DB::connection('mysql3')->select("SHOW COLUMNS FROM " . $table);

        $oldColumns = array_map(function ($column) { return (array) $column->Field ?? $column->Field; }, $oldColumns_connection);
        $newColumns = array_map(function ($column) { return (array) $column->Field ?? $column->Field; }, $newColumns_connection);
    
        return view('associacao_colunas', compact('oldTables', 'newTables', 'oldColumns', 'newColumns', 'oldDatabaseName', 'newDatabaseName', 'table'));
    }

    public function copyData(Request $request){
        
        dd($request->all());
        
    }

    public function compatsImport(){

        $oldConnection = 'mysql2';
        $newConnection = 'mysql';
        
        $compatibilities = DB::connection($oldConnection)
            ->table('ps_ukoocompat_compat as c')
            ->leftJoin('ps_ukoocompat_compat_criterion as cc', 'c.id_ukoocompat_compat', '=', 'cc.id_ukoocompat_compat')
            ->select(
                'c.id_ukoocompat_compat',
                'c.id_product',
                DB::raw("MAX(CASE WHEN cc.id_ukoocompat_filter = 1 THEN cc.id_ukoocompat_criterion END) AS Brand"),
                DB::raw("MAX(CASE WHEN cc.id_ukoocompat_filter = 2 THEN cc.id_ukoocompat_criterion END) AS Model"),
                DB::raw("MAX(CASE WHEN cc.id_ukoocompat_filter = 3 THEN cc.id_ukoocompat_criterion END) AS Type"),
                DB::raw("MAX(CASE WHEN cc.id_ukoocompat_filter = 4 THEN cc.id_ukoocompat_criterion END) AS Version")
            )
            ->groupBy('c.id_ukoocompat_compat', 'c.id_product')
            ->get();

        $insertQueries = [];
    
        foreach ($compatibilities as $compat) {

            $idCompat = DB::connection($newConnection)->table('compats')
                ->where('id_brand', $compat->Brand)
                ->where('id_model', $compat->Model)
                ->where('id_type', $compat->Type)
                ->where('id_version', $compat->Version)
                ->value('id_compat');
    
            if ($idCompat) {
                $insertQueries[] = "INSERT INTO compats_product (`id_compat`, `id_product`, `store`, `created_at`, `updated_at`) VALUES ('$idCompat', '$compat->id_product', '2', NOW(), NOW());";
                echo "<br>INSERT INTO compats_product (`id_compat`, `id_product`, `store`, `created_at`, `updated_at`) VALUES ('$idCompat', '$compat->id_product', '2', NOW(), NOW());";
            }
        }
        
        echo '<br><br>DONE!';
        exit;
        
    }
    
    public function compareTableIDs()
    {

        $oldConnection = 'mysql2';
        $newConnection = 'mysql3';

        $lastInsertedIds = [];

        $allowed_tables = [
            'ps_accessory',
            'ps_address',
            'ps_ASM_ukoo_customer',
            'ps_attachment',
            'ps_attachment_lang',
            'ps_attribute',
            'ps_bms_procurement_product',
            'ps_bms_procurement_purchase_order',
            'ps_bms_procurement_purchase_order_product',
            'ps_bms_procurement_purchase_order_reception',
            'ps_bms_procurement_purchase_order_reception_product',
            'ps_bms_procurement_storage',
            'ps_country_lang',
            'ps_customer',
            'ps_customer_group',
            'ps_date_range',
            'ps_mail',
            'ps_image',
            'ps_message',
            'ps_nacex_expediciones',
            'ps_order_carrier',
            'ps_order_cart_rule',
            'ps_order_detail',
            'ps_order_detail_tax',
            'ps_order_history',
            'ps_order_invoice',
            'ps_order_invoice_payment',
            'ps_order_invoice_tax',
            'ps_order_message',
            'ps_order_message_lang',
            'ps_order_payment',
            'ps_orders',
            'ps_product',
            'ps_product_attachment',
            'ps_product_attribute',
            'ps_product_attribute_combination',
            'ps_product_attribute_image',
            'ps_product_attribute_shop',
            'ps_product_comment',
            'ps_product_comment_grade',
            'ps_product_deleted',
            'ps_product_lang',
            'ps_product_sale',
            'ps_product_shop',
            'ps_product_supplier',
            'ps_product_tag',
            'ps_specific_price',
            'ps_specific_price_priority',
            'ps_statssearch',
            'ps_stock_available',
            'ps_tax',
            'ps_tax_lang',
            'ps_tax_rule',
            'ps_tax_rules_group',
            'ps_tax_rules_group_shop',
            'ps_ups_order_information',
        ];

        try {
            $oldTables = DB::connection($oldConnection)->select("SHOW TABLES");
            $oldTableNames = array_map('current', json_decode(json_encode($oldTables), true));
        } catch (\Exception $e) {
            \Log::error('Erro ao recuperar tabelas da base antiga: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao acessar a base de dados antiga.']);
        }

        foreach ($oldTableNames as $table) {
            
            if( in_array($table, $allowed_tables) ){
                
                $k_id_shop = null;
                $k_id_lang = null;
                $insert_queries = array();
                
                try {
                    $columns = DB::connection($oldConnection)->select("SHOW COLUMNS FROM `$table`");
                    $idColumn = $columns[0]->Field; // Assume-se que a primeira coluna é o ID
        
                    $lastOldId = DB::connection($oldConnection)->table($table)->orderBy($idColumn, 'desc')->value($idColumn);
        
                    $newTables = DB::connection($newConnection)->select("SHOW TABLES");
                    $newTableNames = array_map('current', json_decode(json_encode($newTables), true));
        
                    if (in_array($table, $newTableNames)) {
                        $lastNewId = DB::connection($newConnection)->table($table)->orderBy($idColumn, 'desc')->value($idColumn);
    
                        if( $lastOldId > $lastNewId ){
        
                            $inserts_data = DB::connection($oldConnection)->table($table)->where($idColumn, '>', $lastNewId)->orderBy($idColumn, 'desc')->get();
                            
                            foreach($inserts_data AS $new){
                                
                                $columns_array = array();
                                $values_array = array();
                                
                                foreach($columns AS $key => $column){
                                    
                                    if( $column->Field == 'id_shop' ) $k_id_shop = $key;
                                    if( $column->Field == 'id_lang' ) $k_id_lang = $key;
                                    
                                    if( $column->Field == 'length' ){
                                        $columns_array[] = addslashes('depth');
                                    }else{
                                        $columns_array[] = addslashes($column->Field);
                                    }
                                }

                                foreach($columns_array AS $column){
                                    
                                    if( (($column == 'cover') || ($column == 'default_on') || ($column == 'stock_arrivepa') || ($column == 'fecha_baja') ) && ( is_null($new->$column))){
                                        $values_array[] = "NULL";
                                    }else{
                                        $values_array[] = "'" . addslashes($new->$column) . "'";
                                    }
                                }
                                
                                if( (!is_null($k_id_shop)) && ( $values_array[$k_id_shop] == "'" . 1 . "'" ) ) $values_array[$k_id_shop] = "'" . 2 . "'";
                                
                                
                                if( (!is_null($k_id_lang)) && ( $values_array[$k_id_lang] == "'" . 1 . "'" ) ) $values_array[$k_id_lang] = "'" . 2 . "'";
                                
                                echo '<br><br>' . $insert_queries[] = "INSERT INTO `$table` (" . implode(', ', $columns_array) . ") VALUES (" . implode(', ', $values_array) . ");";
                            }
                        }
                    
                    } else {
                        $lastNewId = '';
                    }
                    
                    if(count($insert_queries) > 0){
                        $lastInsertedIds[$table] = [
                            'id_column' => $idColumn,
                            'last_old_id' => $lastOldId ?? 'Sem registos',
                            'last_new_id' => $lastNewId ?? 'Sem registos',
                            'insert_queries' => $insert_queries
                        ];
                    }
                    
                } catch (\Exception $e) {
                    \Log::error("Erro ao processar a tabela $table: " . $e->getMessage());
                    $lastInsertedIds[$table] = [
                        'error' => 'Erro ao obter dados'
                    ];
                }
            }
        }
        
        echo '<br><br>DONE!';
        exit;
        
        return view('compare_last_ids', compact('lastInsertedIds'));
        
    }
    

    public function compareTableData()
    {
        $oldConnection = 'mysql2';  // Base de dados antiga
        $newConnection = 'mysql3';  // Base de dados nova


        $oldTables = DB::connection($oldConnection)->select("SHOW TABLES");
        $oldTableNames = array_map('current', json_decode(json_encode($oldTables), true));
        
        $oldTableNames = array_slice($oldTableNames, 5, 6);

        $firstColumns = array();
        
        foreach ($oldTableNames as $table) {
            $columns = DB::connection($oldConnection)->select("SHOW COLUMNS FROM `$table`");
            $firstColumn = $columns[0]->Field;
            $firstColumns[$table] = $firstColumn;
        }
    
        $differences = [
            'only_in_old' => [],
            'insert_queries' => [],
            'table_not_found_in_new' => []  // Para armazenar tabelas que não existem na base nova
        ];
            
        foreach ($oldTableNames as $table) {

            $newTableExists = DB::connection($newConnection)->select("SHOW TABLES LIKE '$table'");
            
            if (empty($newTableExists)) {
                $differences['table_not_found_in_new'][] = $table;
                continue;
            }
            
            $firstColumn = $firstColumns[$table];  // A primeira coluna de cada tabela
            
            $oldData = DB::connection($oldConnection)->table($table)->get();
            
            if($table == 'ps_address') dd($oldData);
            
            foreach ($oldData as $oldRow) {

                $idValue = $oldRow->$firstColumn;
                
                $newRow = DB::connection($newConnection)->table($table)->where($firstColumn, $idValue)->first();
            
                if (!$newRow) {

                    $differences['only_in_old'][$idValue] = $oldRow;

                    $columns = [];
                    $values = [];
    
                    foreach ($oldRow as $key => $value) {
                        $columns[] = $key;
                        $values[] = is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                    }
    
                    $insertSql = "INSERT INTO `$table` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");";
                    $differences['insert_queries'][] = $insertSql;
                }
            
            }
        }

        return view('compare_db_content', compact('differences'));
    }

}
