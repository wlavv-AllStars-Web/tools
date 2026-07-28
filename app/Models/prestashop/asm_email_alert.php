<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        $alertTable = self::tableName('asm_email_alert');
        $productTable = self::tableName('product');
        $productAttributeTable = self::tableName('product_attribute');
        $combinationTable = self::tableName('product_attribute_combination');
        $attributeLangTable = self::tableName('attribute_lang');
        $stockTable = self::tableName('stock_available');
        $packTable = self::tableName('pack');
        $shopId = (int) config('allstars.stores.ASM.id_shop', 2);
        $languageId = (int) (config('app.id_lang') ?: 1);

        $requests = self::select(
                $alertTable . '.*',
                DB::raw($productTable . '.reference AS product_reference'),
                DB::raw($productAttributeTable . '.reference AS attr_reference'),
                DB::raw($alertTable . '.date_add AS alert_date_add'),
                $productTable . '.cache_is_pack'
            )
            ->leftJoin($productTable, $alertTable . '.id_product', '=', $productTable . '.id_product')
            ->leftJoin($productAttributeTable, $alertTable . '.id_combination', '=', $productAttributeTable . '.id_product_attribute')
            ->get();

        $combinationIds = $requests
            ->pluck('id_combination')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $combinationNames = $combinationIds->isEmpty()
            ? collect()
            : DB::connection('mysql2')
                ->table($combinationTable . ' AS pac')
                ->join($attributeLangTable . ' AS al', function ($join) use ($languageId) {
                    $join->on('al.id_attribute', '=', 'pac.id_attribute')
                        ->where('al.id_lang', $languageId);
                })
                ->whereIn('pac.id_product_attribute', $combinationIds->all())
                ->orderBy('pac.id_product_attribute')
                ->orderBy('pac.id_attribute')
                ->get(['pac.id_product_attribute', 'al.name'])
                ->groupBy('id_product_attribute')
                ->map(fn ($rows) => $rows->pluck('name')->filter()->implode(' | '));

        $packProductIds = $requests
            ->filter(fn ($request) => (int) $request->cache_is_pack === 1)
            ->pluck('id_product')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $packs = $packProductIds->isEmpty()
            ? collect()
            : DB::connection('mysql2')
                ->table($packTable)
                ->whereIn('id_product_pack', $packProductIds->all())
                ->orderBy('id_product_pack')
                ->orderBy('id_product_item')
                ->get(['id_product_pack', 'quantity', 'id_product_item', 'id_product_attribute_item'])
                ->groupBy('id_product_pack')
                ->map(fn ($rows) => $rows->first());

        $stockKeys = $requests
            ->map(function ($request) use ($packs) {
                $pack = $packs->get((int) $request->id_product);

                if ((int) $request->cache_is_pack === 1 && $pack) {
                    return (int) $pack->id_product_item . ':' . (int) $pack->id_product_attribute_item;
                }

                return (int) $request->id_product . ':' . (int) $request->id_combination;
            })
            ->unique()
            ->values();

        $stockProductIds = $stockKeys
            ->map(fn ($key) => (int) explode(':', $key, 2)[0])
            ->unique()
            ->values();

        $stocks = $stockProductIds->isEmpty()
            ? collect()
            : DB::connection('mysql2')
                ->table($stockTable)
                ->where('id_shop', $shopId)
                ->whereIn('id_product', $stockProductIds->all())
                ->get(['id_product', 'id_product_attribute', 'quantity'])
                ->keyBy(fn ($row) => (int) $row->id_product . ':' . (int) $row->id_product_attribute);

        $data = $requests
            ->map(function ($request) use ($combinationNames, $packs, $stocks) {
                $pack = $packs->get((int) $request->id_product);
                $isPack = (int) $request->cache_is_pack === 1;
                $stockKey = $isPack && $pack
                    ? (int) $pack->id_product_item . ':' . (int) $pack->id_product_attribute_item
                    : (int) $request->id_product . ':' . (int) $request->id_combination;
                $stockQuantity = (int) ($stocks->get($stockKey)->quantity ?? 0);
                $combination = (string) $combinationNames->get((int) $request->id_combination, '');
                $reference = $request->attr_reference ?: $request->product_reference;
                $date = date_create($request->alert_date_add);

                if ($combination !== '') {
                    $combination = ' - <span style="color: red;">' . $combination . '</span>';
                }

                return [
                    'delete' => $request->id,
                    'id_product' => $request->id_product,
                    'reference' => $reference . $combination,
                    'cache_is_pack' => $request->cache_is_pack,
                    'stock' => $isPack
                        ? (($pack->quantity ?? 0) . ' <span style="color: red;">( ' . $stockQuantity . ' )</span> ')
                        : $stockQuantity,
                    'pack_quantity' => $pack->quantity ?? 0,
                    'date' => $date ? date_format($date, 'Y-m-d') : '',
                    'send_email' => $request->id,
                    'email' => $request->email,
                    '_stock_quantity' => $stockQuantity,
                ];
            })
            ->sortByDesc('_stock_quantity')
            ->map(function ($row) {
                unset($row['_stock_quantity']);
                return $row;
            })
            ->values()
            ->all();

        return [
            'name' => trans('dashboard.Products requested'),
            'col' => 4,
            'item_id' => $type . '_products_requested',
            'prestashop' => self::adminProductLink('ASM'),
            'columns' => ['delete', 'reference', 'stock', 'cache_is_pack', 'pack_quantity', 'email', 'send_email'],
            'table' => 'asm_email_alert',
            'counter' => count($data),
            'data' => $data,
        ];
    }
}
