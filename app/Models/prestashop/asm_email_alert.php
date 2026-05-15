<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class asm_email_alert extends PrestashopModel{
    
    use HasFactory;

    protected $fillable = ['name'];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('asm_email_alert');
    }

    public static function dashboard_product_request($type)
    {
        $data = [];

        $alertTable = self::tableName('asm_email_alert');
        $productTable = self::tableName('product');
        $productAttributeTable = self::tableName('product_attribute');
        $stockTable = self::tableName('stock_available');

        $clients_request = self::select(
                $alertTable . '.*',
                DB::raw($productTable . '.reference AS product_reference'),
                DB::raw($productAttributeTable . '.reference AS attr_reference'),
                DB::raw($alertTable . '.date_add AS alert_date_add'),
                $productTable . '.cache_is_pack'
            )
            ->leftJoin($productTable, $alertTable . '.id_product', '=', $productTable . '.id_product')
            ->leftJoin($productAttributeTable, $alertTable . '.id_combination', '=', $productAttributeTable . '.id_product_attribute')
            ->leftJoin($stockTable, function ($join) use ($alertTable, $stockTable) {
                $join->on($alertTable . '.id_product', '=', $stockTable . '.id_product');
                $join->on($alertTable . '.id_combination', '=', $stockTable . '.id_product_attribute');
            })
            ->orderBy($stockTable . '.quantity', 'DESC')
            ->get();

        foreach ($clients_request as $request) {
            $combination = '';

            $reference = is_null($request->attr_reference) ? $request->product_reference : $request->attr_reference;

            if ($request->id_combination > 0) {
                $combination_data = product_attribute_combination::with('attribute_lang')
                    ->where('id_product_attribute', $request->id_combination)
                    ->orderBy('id_product_attribute', 'DESC')
                    ->get();

                foreach ($combination_data as $attr) {
                    $combination .= $attr->attribute_lang->name . ' | ';
                }

                $combination = substr($combination, 0, -3);
            }

            $pack = pack::select('quantity', 'id_product_item', 'id_product_attribute_item')->where('id_product_pack', $request->id_product)->first();

            if ((int) $request->cache_is_pack === 1 && $pack) {
                $stock = stock_available::select('quantity')->where('id_product', $pack->id_product_item)->where('id_product_attribute', $pack->id_product_attribute_item)->first();
            } else {
                $stock = stock_available::select('quantity')->where('id_product', $request->id_product)->where('id_product_attribute', $request->id_combination)->first();
            }

            $date = date_create($request->alert_date_add);

            $combination = strlen($combination)
                ? ' - <span style="color: red;">' . $combination . '</span>'
                : '';

            $product = [
                'delete' => $request->id,
                'id_product' => $request->id_product,
                'reference' => $reference . $combination,
                'cache_is_pack' => $request->cache_is_pack,
                'stock' => ((int) $request->cache_is_pack === 1)
                    ? (($pack->quantity ?? 0) . ' <span style="color: red;">( ' . ($stock->quantity ?? 0) . ' )</span> ')
                    : ($stock->quantity ?? 0),
                'pack_quantity' => $pack->quantity ?? 0,
                'date' => $date ? date_format($date, "Y-m-d") : '',
                'send_email' => $request->id,
                'email' => $request->email,
            ];

            $data[] = $product;
        }

        return [
            'name' => trans('dashboard.Products requested'),
            'col' => 4,
            'item_id' => $type . '_products_requested',
            'prestashop' => (isset(Config::get('token')->AdminProducts))
                ? [
                    'token' => Config::get('token')->AdminProducts,
                    'controller' => 'AdminProducts',
                    'element' => 'id_product',
                    'extraParameters' => '&updateproduct'
                ]
                : [],
            'columns' => ['delete', 'reference', 'stock', 'cache_is_pack', 'pack_quantity', 'email', 'send_email'],
            'table' => 'asm_email_alert',
            'counter' => count($data),
            'data' => $data,
        ];
    }
}