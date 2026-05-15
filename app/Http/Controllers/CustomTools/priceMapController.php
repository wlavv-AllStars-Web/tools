<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Models\modules\price_map\price_map;
use App\Models\prestashop\manufacturers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class priceMapController extends Controller
{
    public $actions;
    public $breadcrumbs;

    public function index()
    {
        $indexRoute = request()->routeIs('purchase.tools.price_map.*')
            ? 'purchase.tools.price_map.index'
            : (request()->routeIs('backoffice.tools.price_map.*') ? 'backoffice.tools.price_map.index' : 'priceMap.index');

        $this->breadcrumbs[] = ['name' => 'purchase', 'url' => route('purchase.index')];
        $this->breadcrumbs[] = ['name' => 'Price map', 'url' => route($indexRoute), 'no_translation' => 1];

        $brands = manufacturers::orderBy('name', 'ASC')->get();

        $data = [
            'actions' => [],
            'breadcrumbs' => $this->breadcrumbs,
            'brands' => $brands,
        ];

        return View::make('customTools/priceMap/index')->with($data);
    }

    public function getPriceMapOfBrand(Request $request)
    {
        $products = self::buildPriceMapProducts((int) $request->id_manufacturer);

        self::generateCSV($products, $request->id_manufacturer);

        unset($products['supplier'], $products['manufacturer']);

        $html = view('customTools/priceMap/priceMapBrand', compact('products'))->render();

        return response()->json(['html' => $html]);
    }

    public static function generateCSV($products, $id_manufacturer)
    {
        $fileName = 'ASD_' . $id_manufacturer . '.csv';
        $filePath = 'catalogue/' . $fileName;

        $header_names = ["Supplier", "Manufacturer", "SKU", "Name", "VAT", "Price", "Purchase", "Width", "Height", "Depth", "Weight", "Discount", "Tags", "Meta title", "Meta Tags", "URL"];

        $csvData = fopen('php://temp', 'r+');
        fwrite($csvData, "\xEF\xBB\xBF");
        fputcsv($csvData, $header_names, ';');

        $supplier = $products['supplier'] ?? '';
        $manufacturer = $products['manufacturer'] ?? '';

        foreach ($products as $row) {
            if (!is_array($row)) {
                continue;
            }

            if (!empty($row['reference']) && empty($row['asd_reference'])) {
                $reference = $row['reference'];
                $attrRef = $row['attr_reference'];
                $asm_ref = !empty($attrRef) ? $attrRef : $reference;
                $tags = $manufacturer . ', ' . $reference . ', ' . $attrRef;
                $url = self::sanitizeForUrl($manufacturer . '-' . $asm_ref);

                fputcsv($csvData, [
                    $supplier,
                    $manufacturer,
                    $asm_ref,
                    '',
                    7,
                    str_replace([' ', 'EUR'], '', $row['price']),
                    str_replace([' ', 'EUR'], '', $row['wholesale_price']),
                    $row['width'],
                    $row['height'],
                    $row['depth'],
                    $row['weight'],
                    '',
                    $tags,
                    '',
                    $tags,
                    $url,
                ], ';');
            }
        }

        rewind($csvData);
        $csvString = stream_get_contents($csvData);
        fclose($csvData);

        Storage::disk('public_uploads')->put($filePath, $csvString);
    }

    public static function sanitizeForUrl($string)
    {
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9]+/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        return trim($string, '-');
    }

    public function cron_priceMap($part = 0)
    {
        $all_manufacturers = manufacturers::orderBy('id_manufacturer', 'ASC')->get();
        $total = $all_manufacturers->count();
        $per_part = ceil($total / 10);
        $manufacturers = $all_manufacturers->slice($part * $per_part, $per_part);

        foreach ($manufacturers as $manufacturer) {
            price_map::emptyTableFor($manufacturer->id_manufacturer);

            foreach (self::buildPriceMapComparisonRows((int) $manufacturer->id_manufacturer) as $product_compare) {
                $save = 0;

                if ((!isset($product_compare['asm_racio'])) || ($product_compare['asm_racio']) < 5.01) {
                    $save = 1;
                }

                if ((!isset($product_compare['asd_racio'])) || ($product_compare['asd_racio']) < 5.01) {
                    $save = 1;
                }

                if ($save == 1) {
                    price_map::saveData($manufacturer->id_manufacturer, $manufacturer->name, $product_compare);
                }
            }
        }
    }

    private static function buildPriceMapProducts(int $idManufacturer): array
    {
        $products = [];
        $rows = self::priceRowsForManufacturer($idManufacturer);
        $products['supplier'] = $rows->first()->supplier ?? '';
        $products['manufacturer'] = $rows->first()->manufacturer ?? '';

        foreach ($rows as $row) {
            $asmPrice = self::effectivePrice($row->asm_product_price, $row->asm_attribute_price);
            $asmWholesale = self::effectiveWholesale($row->asm_product_wholesale, $row->asm_attribute_wholesale);
            $asdPrice = self::effectivePrice($row->asd_product_price, $row->asd_attribute_price);
            $asdWholesale = self::effectiveWholesale($row->asd_product_wholesale, $row->asd_attribute_wholesale);

            $products[(int) $row->id_product . ':' . (int) $row->id_product_attribute] = [
                'active' => (int) $row->asm_active,
                'reference' => (string) $row->reference,
                'attr_reference' => (string) ($row->attr_reference ?? ''),
                'deprecated' => (int) $row->deprecated,
                'price' => self::formatMoney($asmPrice),
                'wholesale_price' => self::formatMoney($asmWholesale),
                'discount' => self::formatPercent((float) ($row->asm_discount ?? 0) * 100),
                'racio' => self::formatRatio($asmWholesale, $asmPrice),
                'width' => $row->width,
                'height' => $row->height,
                'depth' => $row->depth,
                'weight' => $row->weight,
                'asd_active' => (int) ($row->asd_active ?? 0),
                'asd_reference' => (string) $row->reference,
                'asd_attr_reference' => (string) ($row->attr_reference ?? ''),
                'asd_deprecated' => (int) $row->deprecated,
                'asd_price' => self::formatMoney($asdPrice),
                'asd_wholesale_price' => self::formatMoney($asdWholesale),
                'asd_discount' => self::formatPercent((float) ($row->asd_discount ?? 0) * 100),
                'asd_racio' => self::formatRatio($asdWholesale, $asdPrice),
                'asd_supplier' => (string) ($row->supplier ?? ''),
                'asd_manufacturer' => (string) ($row->manufacturer ?? ''),
            ];
        }

        return $products;
    }

    private static function buildPriceMapComparisonRows(int $idManufacturer): array
    {
        return self::priceRowsForManufacturer($idManufacturer)->map(function ($row) {
            $asmPrice = self::effectivePrice($row->asm_product_price, $row->asm_attribute_price);
            $asmWholesale = self::effectiveWholesale($row->asm_product_wholesale, $row->asm_attribute_wholesale);
            $asdPrice = self::effectivePrice($row->asd_product_price, $row->asd_attribute_price);
            $asdWholesale = self::effectiveWholesale($row->asd_product_wholesale, $row->asd_attribute_wholesale);

            return [
                'asm_reference' => $row->id_product_attribute > 0 ? $row->attr_reference : $row->reference,
                'asm_price' => $asmPrice,
                'asm_wholesale_price' => $asmWholesale,
                'asm_active' => (int) $row->asm_active,
                'asm_deprecated' => (int) $row->deprecated,
                'asm_discount' => (float) ($row->asm_discount ?? 0) * 100,
                'asm_racio' => self::ratio($asmWholesale, $asmPrice),
                'asd_price' => $asdPrice,
                'asd_wholesale_price' => $asdWholesale,
                'asd_active' => (int) ($row->asd_active ?? 0),
                'asd_deprecated' => (int) $row->deprecated,
                'asd_discount' => (float) ($row->asd_discount ?? 0) * 100,
                'asd_racio' => self::ratio($asdWholesale, $asdPrice),
            ];
        })->all();
    }

    private static function priceRowsForManufacturer(int $idManufacturer)
    {
        $prefix = env('DB2_DB_prefix', env('DB2_prefix', 'ps_'));
        $hasDeprecatedColumn = Schema::connection('mysql2')->hasColumn($prefix . 'product', 'wmdeprecated');
        $deprecatedColumn = $hasDeprecatedColumn ? 'p.wmdeprecated' : '0';
        $groupBy = ['p.id_product', 'pa.id_product_attribute', 'p.reference', 'pa.reference', 'ps_asm.active', 'ps_asd.active', 'ps_asm.price', 'ps_asd.price', 'p.price', 'pas_asm.price', 'pas_asd.price', 'pa.price', 'ps_asm.wholesale_price', 'ps_asd.wholesale_price', 'p.wholesale_price', 'pas_asm.wholesale_price', 'pas_asd.wholesale_price', 'pa.wholesale_price', 'p.width', 'p.height', 'p.depth', 'p.weight', 'm.name', 's.name'];

        if ($hasDeprecatedColumn) {
            $groupBy[] = 'p.wmdeprecated';
        }

        return DB::connection('mysql2')
            ->table($prefix . 'product as p')
            ->join($prefix . 'product_shop as ps_asm', function ($join) {
                $join->on('ps_asm.id_product', '=', 'p.id_product')->where('ps_asm.id_shop', 2);
            })
            ->leftJoin($prefix . 'product_shop as ps_asd', function ($join) {
                $join->on('ps_asd.id_product', '=', 'p.id_product')->where('ps_asd.id_shop', 3);
            })
            ->leftJoin($prefix . 'product_attribute as pa', 'pa.id_product', '=', 'p.id_product')
            ->leftJoin($prefix . 'product_attribute_shop as pas_asm', function ($join) {
                $join->on('pas_asm.id_product_attribute', '=', 'pa.id_product_attribute')->where('pas_asm.id_shop', 2);
            })
            ->leftJoin($prefix . 'product_attribute_shop as pas_asd', function ($join) {
                $join->on('pas_asd.id_product_attribute', '=', 'pa.id_product_attribute')->where('pas_asd.id_shop', 3);
            })
            ->leftJoin($prefix . 'specific_price as sp_asm', function ($join) {
                $join->on('sp_asm.id_product', '=', 'p.id_product')
                    ->whereRaw('COALESCE(sp_asm.id_product_attribute, 0) = COALESCE(pa.id_product_attribute, 0)')
                    ->whereIn('sp_asm.id_shop', [0, 2])
                    ->where('sp_asm.reduction', '>', 0);
            })
            ->leftJoin($prefix . 'specific_price as sp_asd', function ($join) {
                $join->on('sp_asd.id_product', '=', 'p.id_product')
                    ->whereRaw('COALESCE(sp_asd.id_product_attribute, 0) = COALESCE(pa.id_product_attribute, 0)')
                    ->whereIn('sp_asd.id_shop', [0, 3])
                    ->where('sp_asd.reduction', '>', 0);
            })
            ->leftJoin($prefix . 'manufacturer as m', 'm.id_manufacturer', '=', 'p.id_manufacturer')
            ->leftJoin($prefix . 'supplier as s', 's.id_supplier', '=', 'p.id_supplier')
            ->where('p.id_manufacturer', $idManufacturer)
            ->select([
                'p.id_product',
                DB::raw('COALESCE(pa.id_product_attribute, 0) as id_product_attribute'),
                'p.reference',
                DB::raw('COALESCE(pa.reference, "") as attr_reference'),
                DB::raw($deprecatedColumn . ' as deprecated'),
                DB::raw('COALESCE(ps_asm.active, 0) as asm_active'),
                DB::raw('COALESCE(ps_asd.active, 0) as asd_active'),
                DB::raw('COALESCE(ps_asm.price, p.price, 0) as asm_product_price'),
                DB::raw('COALESCE(ps_asd.price, p.price, 0) as asd_product_price'),
                DB::raw('COALESCE(pas_asm.price, pa.price, 0) as asm_attribute_price'),
                DB::raw('COALESCE(pas_asd.price, pa.price, 0) as asd_attribute_price'),
                DB::raw('COALESCE(ps_asm.wholesale_price, p.wholesale_price, 0) as asm_product_wholesale'),
                DB::raw('COALESCE(ps_asd.wholesale_price, p.wholesale_price, 0) as asd_product_wholesale'),
                DB::raw('COALESCE(pas_asm.wholesale_price, pa.wholesale_price, 0) as asm_attribute_wholesale'),
                DB::raw('COALESCE(pas_asd.wholesale_price, pa.wholesale_price, 0) as asd_attribute_wholesale'),
                DB::raw('MAX(COALESCE(sp_asm.reduction, 0)) as asm_discount'),
                DB::raw('MAX(COALESCE(sp_asd.reduction, 0)) as asd_discount'),
                'p.width',
                'p.height',
                'p.depth',
                'p.weight',
                DB::raw('COALESCE(m.name, "") as manufacturer'),
                DB::raw('COALESCE(s.name, "") as supplier'),
            ])
            ->groupBy(...$groupBy)
            ->orderBy('p.reference')
            ->orderBy('pa.reference')
            ->get();
    }

    private static function effectivePrice($productPrice, $attributePrice): float
    {
        return (float) $productPrice + max((float) $attributePrice, 0);
    }

    private static function effectiveWholesale($productWholesale, $attributeWholesale): float
    {
        return (float) $attributeWholesale > 0 ? (float) $attributeWholesale : (float) $productWholesale;
    }

    private static function ratio(float $wholesale, float $price): float
    {
        return $price > 0 ? (1 - ($wholesale / $price)) * 100 : 0;
    }

    private static function formatRatio(float $wholesale, float $price): string
    {
        return number_format(self::ratio($wholesale, $price), 2, '.', ' ');
    }

    private static function formatMoney(float $value): string
    {
        return number_format($value, 2, '.', ' ') . ' EUR';
    }

    private static function formatPercent(float $value): string
    {
        return number_format($value, 2, '.', ' ') . ' %';
    }
}
