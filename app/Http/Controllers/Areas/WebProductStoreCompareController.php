<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WebProductStoreCompareController extends Controller
{
    private const ASM_CONNECTION = 'temporary_store_compare_asm';
    private const ASD_CONNECTION = 'temporary_store_compare_asd';

    private const STORES = [
        'asm' => [
            'connection' => self::ASM_CONNECTION,
            'label' => 'ASM',
            'database' => 'allstar1_01062026',
            'prefix' => 'ps_',
        ],
        'asd' => [
            'connection' => self::ASD_CONNECTION,
            'label' => 'ASD',
            'database' => 'allstarsdistribution',
            'prefix' => 'psnz_',
        ],
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->configureTemporaryConnections();

        $brand = trim((string) $request->query('brand', ''));
        $brands = $this->brands();
        $rows = $brand !== '' ? $this->comparisonRows($brand) : [];

        return $this->view($brand, $brands, $rows);
    }

    public function uploadCatalog(Request $request)
    {
        $this->configureTemporaryConnections();

        $brand = trim((string) $request->input('brand', ''));
        $brands = $this->brands();
        $rows = $brand !== '' ? $this->comparisonRows($brand) : [];
        $catalog = $request->hasFile('catalog') ? $this->supplierCatalog($request->file('catalog')->getRealPath()) : [];

        $request->session()->put('product_store_compare_supplier_catalog', [
            'brand' => $brand,
            'catalog' => $catalog,
            'name' => $request->file('catalog')?->getClientOriginalName(),
        ]);

        $rows = $this->applySupplierCatalog($rows, $catalog);
        $pendingImports = $this->pendingSupplierImports($rows, $catalog);

        return $this->view(
            $brand,
            $brands,
            $rows,
            [
                'name' => $request->file('catalog')?->getClientOriginalName(),
                'count' => count($catalog),
                'pending_imports' => $pendingImports,
            ]
        );
    }

    private function view(string $brand, array $brands, array $rows, ?array $supplierCatalog = null)
    {
        return View::make('areas.web.product-store-compare', [
            'brand' => $brand,
            'brands' => $brands,
            'rows' => $rows,
            'counters' => $this->counters($rows),
            'supplierManufacturerInfo' => $this->supplierManufacturerInfo($rows),
            'supplierCatalog' => $supplierCatalog,
            'breadcrumbs' => [
                ['name' => trans('web'), 'url' => route('web.index')],
                ['name' => 'Store product compare', 'url' => route('web.product_store_compare.index'), 'no_translation' => 1],
            ],
        ]);
    }

    public function csv(Request $request): StreamedResponse
    {
        $this->configureTemporaryConnections();

        $brand = trim((string) $request->query('brand', ''));
        $rows = $brand !== '' ? $this->comparisonRows($brand) : [];
        $filename = 'store_product_compare_' . $this->filenamePart($brand) . '_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $this->csvHeaders());

            foreach ($rows as $row) {
                fputcsv($handle, $this->csvRow($row));
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function pdf(Request $request): Response
    {
        $this->configureTemporaryConnections();

        $brand = trim((string) $request->query('brand', ''));
        $rows = $brand !== '' ? $this->comparisonRows($brand) : [];
        $supplierCatalog = $request->hasSession()
            ? $request->session()->get('product_store_compare_supplier_catalog')
            : null;
        $hasSupplierCatalog = ($supplierCatalog['brand'] ?? null) === $brand && ! empty($supplierCatalog['catalog']);

        if ($hasSupplierCatalog) {
            $rows = $this->applySupplierCatalog($rows, $supplierCatalog['catalog']);
        }

        $title = 'Store product compare' . ($brand !== '' ? ' - ' . $brand : '');
        $filename = 'store_product_compare_' . $this->filenamePart($brand) . '_' . date('Ymd_His') . '.pdf';

        return response($this->simplePdf($title, $rows, $hasSupplierCatalog), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function configureTemporaryConnections(): void
    {
        foreach (self::STORES as $store) {
            $connection = config('database.connections.mysql');
            $connection['database'] = $store['database'];

            config(['database.connections.' . $store['connection'] => $connection]);
            DB::purge($store['connection']);
        }
    }

    private function brands(): array
    {
        $brands = [];

        foreach (self::STORES as $store) {
            $prefix = $store['prefix'];
            $rows = DB::connection($store['connection'])->select(
                "SELECT DISTINCT m.name
                 FROM {$prefix}product p
                 INNER JOIN {$prefix}manufacturer m ON m.id_manufacturer = p.id_manufacturer
                 WHERE p.reference IS NOT NULL AND p.reference <> ''
                 ORDER BY m.name"
            );

            foreach ($rows as $row) {
                if (! empty($row->name)) {
                    $brands[$row->name] = $row->name;
                }
            }
        }

        ksort($brands, SORT_NATURAL | SORT_FLAG_CASE);

        return array_values($brands);
    }

    private function comparisonRows(string $brand): array
    {
        $asm = $this->productsForStore(self::STORES['asm'], $brand);
        $asd = $this->productsForStore(self::STORES['asd'], $brand);
        $references = array_values(array_unique(array_merge(array_keys($asm), array_keys($asd))));

        sort($references, SORT_NATURAL | SORT_FLAG_CASE);

        return array_map(function (string $reference) use ($asm, $asd) {
            return [
                'reference' => $reference,
                'asm' => $asm[$reference] ?? null,
                'asd' => $asd[$reference] ?? null,
                'exclusive' => isset($asm[$reference]) && isset($asd[$reference])
                    ? ''
                    : (isset($asm[$reference]) ? 'ASM only' : 'ASD only'),
            ];
        }, $references);
    }

    private function productsForStore(array $store, string $brand): array
    {
        $prefix = $store['prefix'];
        $rows = DB::connection($store['connection'])->select(
            "SELECT
                COALESCE(NULLIF(pa.reference, ''), p.reference) AS reference,
                p.id_product,
                CASE WHEN p.cache_is_pack = 1 OR EXISTS (
                    SELECT 1 FROM {$prefix}pack pack_rows WHERE pack_rows.id_product_pack = p.id_product LIMIT 1
                ) THEN 1 ELSE 0 END AS is_pack,
                COALESCE(stock.quantity, 0) AS stock,
                COALESCE(product_supplier.purchase_price, p.wholesale_price, 0) AS purchase_price,
                p.wmdeprecated AS wm_deprecated,
                p.active AS is_active,
                p.visibility,
                p.id_category_default,
                COALESCE(categories.category_names, '') AS category,
                COALESCE(m.name, '') AS brand,
                COALESCE(s.name, '') AS supplier,
                COALESCE(discounts.discount_label, '') AS discount,
                COALESCE(discounts.discount_type, '') AS discount_type,
                COALESCE(discounts.has_global_discount, 0) AS has_global_discount,
                COALESCE(discounts.has_individual_discount, 0) AS has_individual_discount
             FROM {$prefix}product p
             LEFT JOIN {$prefix}product_attribute pa ON pa.id_product = p.id_product
             INNER JOIN {$prefix}manufacturer m ON m.id_manufacturer = p.id_manufacturer
             LEFT JOIN {$prefix}supplier s ON s.id_supplier = p.id_supplier
             LEFT JOIN (
                SELECT id_product, id_product_attribute, SUM(quantity) AS quantity
                FROM {$prefix}stock_available
                GROUP BY id_product, id_product_attribute
             ) stock ON stock.id_product = p.id_product
                AND stock.id_product_attribute = COALESCE(pa.id_product_attribute, 0)
             LEFT JOIN (
                SELECT
                    id_product,
                    id_product_attribute,
                    MAX(product_supplier_price_te) AS purchase_price
                FROM {$prefix}product_supplier
                GROUP BY id_product, id_product_attribute
             ) product_supplier ON product_supplier.id_product = p.id_product
                AND product_supplier.id_product_attribute = COALESCE(pa.id_product_attribute, 0)
             LEFT JOIN (
                SELECT
                    cp.id_product,
                    GROUP_CONCAT(DISTINCT cl.name ORDER BY cl.name SEPARATOR ' | ') AS category_names
                FROM {$prefix}category_product cp
                INNER JOIN {$prefix}product p_categories ON p_categories.id_product = cp.id_product
                INNER JOIN {$prefix}category_lang cl
                    ON cl.id_category = cp.id_category
                    AND cl.id_lang = 1
                    AND cl.id_shop = p_categories.id_shop_default
                GROUP BY cp.id_product
             ) categories ON categories.id_product = p.id_product
             LEFT JOIN (
                SELECT
                    sp.id_product,
                    MAX(CASE WHEN sp.id_specific_price_rule > 0 THEN 1 ELSE 0 END) AS has_global_discount,
                    MAX(CASE WHEN sp.id_specific_price_rule = 0 THEN 1 ELSE 0 END) AS has_individual_discount,
                    CASE
                        WHEN MAX(CASE WHEN sp.id_specific_price_rule = 0 THEN 1 ELSE 0 END) = 1 THEN 'individual'
                        WHEN MAX(CASE WHEN sp.id_specific_price_rule > 0 THEN 1 ELSE 0 END) = 1 THEN 'global'
                        ELSE ''
                    END AS discount_type,
                    GROUP_CONCAT(
                        CASE
                            WHEN sp.reduction_type = 'percentage' THEN CONCAT(ROUND(sp.reduction * 100, 2), '%')
                            ELSE CONCAT(ROUND(sp.reduction, 2), ' amount')
                        END
                        ORDER BY sp.id_specific_price
                        SEPARATOR ' | '
                    ) AS discount_label
                FROM {$prefix}specific_price sp
                WHERE sp.id_product > 0
                    AND (sp.`from` = '0000-00-00 00:00:00' OR sp.`from` <= NOW())
                    AND (sp.`to` = '0000-00-00 00:00:00' OR sp.`to` >= NOW())
                GROUP BY sp.id_product
             ) discounts ON discounts.id_product = p.id_product
             WHERE p.reference IS NOT NULL
                AND p.reference <> ''
                AND m.name = ?
             ORDER BY p.reference",
            [$brand]
        );

        $products = [];
        foreach ($rows as $row) {
            $products[(string) $row->reference] = [
                'id_product' => (int) $row->id_product,
                'is_pack' => (int) $row->is_pack === 1,
                'stock' => (int) $row->stock,
                'purchase_price' => round((float) $row->purchase_price, 2),
                'wm_deprecated' => (int) $row->wm_deprecated === 1,
                'discount' => (string) $row->discount,
                'discount_type' => (string) $row->discount_type,
                'has_global_discount' => (int) $row->has_global_discount === 1,
                'has_individual_discount' => (int) $row->has_individual_discount === 1,
                'is_active' => (int) $row->is_active === 1,
                'visibility' => (string) $row->visibility,
                'category' => (string) $row->category,
                'brand' => (string) $row->brand,
                'supplier' => (string) $row->supplier,
            ];
        }

        return $products;
    }

    private function counters(array $rows): array
    {
        $categories = [];
        $brands = [];

        $counters = [
            'packs' => 0,
            'end_of_life' => 0,
            'global_discounts' => 0,
            'inactive_products' => 0,
            'exclusive_products' => 0,
            'not_visible_products' => 0,
            'existing_categories' => 0,
            'brands' => 0,
            'manufacturers' => 0,
            'total' => count($rows),
        ];

        foreach ($rows as $row) {
            $stores = array_filter([$row['asm'], $row['asd']]);

            if ($row['exclusive'] !== '') {
                $counters['exclusive_products']++;
            }

            if ($this->anyStore($stores, 'is_pack')) {
                $counters['packs']++;
            }

            if ($this->anyStore($stores, 'wm_deprecated')) {
                $counters['end_of_life']++;
            }

            if ($this->anyStore($stores, 'has_global_discount')) {
                $counters['global_discounts']++;
            }

            if ($this->anyStoreValue($stores, 'is_active', false)) {
                $counters['inactive_products']++;
            }

            foreach ($stores as $store) {
                if (($store['visibility'] ?? '') !== 'both') {
                    $counters['not_visible_products']++;
                    break;
                }
            }

            foreach ($stores as $store) {
                if (! empty($store['category'])) {
                    $categories[$store['category']] = true;
                }
                if (! empty($store['brand'])) {
                    $brands[$store['brand']] = true;
                }
            }
        }

        $counters['existing_categories'] = count($categories);
        $counters['brands'] = count($brands);
        $counters['manufacturers'] = count($brands);

        return $counters;
    }

    private function anyStore(array $stores, string $field): bool
    {
        foreach ($stores as $store) {
            if (! empty($store[$field])) {
                return true;
            }
        }

        return false;
    }

    private function anyStoreValue(array $stores, string $field, bool $value): bool
    {
        foreach ($stores as $store) {
            if (($store[$field] ?? null) === $value) {
                return true;
            }
        }

        return false;
    }

    private function csvHeaders(): array
    {
        $storeHeaders = [
            'is_pack',
            'purchase_price',
            'wm_deprecated',
            'discount',
            'is_active',
            'is_exclusive',
            'visibility',
            'category',
        ];

        return array_merge(
            array_map(fn (string $header) => 'asm_' . $header, $storeHeaders),
            ['product_reference'],
            array_map(fn (string $header) => 'asd_' . $header, $storeHeaders)
        );
    }

    private function csvRow(array $row): array
    {
        return array_merge(
            $this->csvStoreCells($row['asm'], $row['exclusive'] === 'ASM only' ? 'yes' : ''),
            [$row['reference']],
            $this->csvStoreCells($row['asd'], $row['exclusive'] === 'ASD only' ? 'yes' : '')
        );
    }

    private function csvStoreCells(?array $store, string $exclusive): array
    {
        if (! $store) {
            return ['', '', '', '', '', '', $exclusive, '', ''];
        }

        return [
            $store['is_pack'] ? 'yes' : 'no',
            number_format((float) $store['purchase_price'], 2, '.', ''),
            $store['wm_deprecated'] ? 'yes' : 'no',
            $store['discount'],
            $store['is_active'] ? 'yes' : 'no',
            $exclusive,
            $store['visibility'],
            $store['category'],
        ];
    }

    private function simplePdf(string $title, array $rows, bool $hasSupplierCatalog = false): string
    {
        $columns = $this->pdfTableColumns($hasSupplierCatalog);
        $pageWidth = 842;
        $marginX = 10;
        $availableWidth = $pageWidth - ($marginX * 2);
        $totalWidth = array_sum(array_column($columns, 'width'));
        $scale = $totalWidth > 0 ? $availableWidth / $totalWidth : 1;
        $columns = array_map(function (array $column) use ($scale) {
            $column['width'] = round($column['width'] * $scale, 2);
            return $column;
        }, $columns);
        $pages = [];
        $pageRows = array_chunk($rows, 49);

        foreach ($pageRows ?: [[]] as $pageIndex => $pageChunk) {
            $content = '';
            $content .= $this->pdfTextAt(24, 572, $title, 11, [0.12, 0.12, 0.12]);
            $content .= $this->pdfTextAt(24, 558, 'Generated at ' . date('Y-m-d H:i:s') . ' - Rows: ' . count($rows), 7, [0.35, 0.35, 0.35]);
            $content .= $this->pdfTextAt(780, 558, 'Page ' . ($pageIndex + 1) . '/' . count($pageRows ?: [[]]), 7, [0.35, 0.35, 0.35]);

            $x = $marginX;
            $y = 532;
            $groupHeaderHeight = 18;
            $columnHeaderHeight = 22;
            $bottomMargin = 18;

            $sideColumnCount = (int) ((count($columns) - 1) / 2);
            $asmWidth = array_sum(array_column(array_slice($columns, 0, $sideColumnCount), 'width'));
            $referenceWidth = $columns[$sideColumnCount]['width'];
            $asdWidth = array_sum(array_column(array_slice($columns, $sideColumnCount + 1), 'width'));

            $content .= $this->pdfCell($x, $y, $asmWidth, $groupHeaderHeight, 'ASM', [0.86, 0.21, 0.27], [1, 1, 1], 8, true);
            $content .= $this->pdfCell($x + $asmWidth, $y, $referenceWidth, $groupHeaderHeight, 'Reference', [0.91, 0.93, 0.94], [0.2, 0.23, 0.25], 7, true);
            $content .= $this->pdfCell($x + $asmWidth + $referenceWidth, $y, $asdWidth, $groupHeaderHeight, 'ASD', [0.12, 0.56, 1], [1, 1, 1], 8, true);

            $y -= $groupHeaderHeight;
            $currentX = $x;
            foreach ($columns as $column) {
                $content .= $this->pdfCell($currentX, $y, $column['width'], $columnHeaderHeight, $column['label'], [0.97, 0.98, 0.98], [0.12, 0.12, 0.12], 5.5, true);
                $currentX += $column['width'];
            }

            $y -= $columnHeaderHeight;
            $rowHeight = count($pageChunk) > 0
                ? min(16, max(9, (($y - $bottomMargin) / count($pageChunk)) / 2))
                : 10;

            foreach ($pageChunk as $row) {
                $currentX = $x;
                $cells = array_merge(
                    $this->pdfStoreCells($row['asm'], $row['exclusive'] === 'ASM only', false, $hasSupplierCatalog, $row),
                    [['text' => $row['reference'], 'fill' => [0.91, 0.93, 0.94], 'bold' => true]],
                    $this->pdfStoreCells($row['asd'], $row['exclusive'] === 'ASD only', true, $hasSupplierCatalog, $row)
                );

                foreach ($columns as $index => $column) {
                    $cell = $cells[$index] ?? ['text' => '-'];
                    $content .= $this->pdfCell(
                        $currentX,
                        $y,
                        $column['width'],
                        $rowHeight,
                        (string) ($cell['text'] ?? '-'),
                        $cell['fill'] ?? [1, 1, 1],
                        $cell['color'] ?? [0.12, 0.12, 0.12],
                        4.8,
                        (bool) ($cell['bold'] ?? false)
                    );
                    $currentX += $column['width'];
                }

                $y -= $rowHeight;
            }

            $pages[] = $content;
        }

        return $this->pdfFromPageContents($pages);
    }

    private function supplierManufacturerInfo(array $rows): array
    {
        $suppliers = [];
        $manufacturers = [];

        foreach ($rows as $row) {
            foreach (array_filter([$row['asm'], $row['asd']]) as $store) {
                if (! empty($store['supplier'])) {
                    $suppliers[$store['supplier']] = true;
                }

                if (! empty($store['brand'])) {
                    $manufacturers[$store['brand']] = true;
                }
            }
        }

        $supplierNames = array_keys($suppliers);
        $manufacturerNames = array_keys($manufacturers);
        sort($supplierNames, SORT_NATURAL | SORT_FLAG_CASE);
        sort($manufacturerNames, SORT_NATURAL | SORT_FLAG_CASE);

        return [
            'same_supplier' => count($supplierNames) <= 1,
            'same_manufacturer' => count($manufacturerNames) <= 1,
            'suppliers' => $supplierNames,
            'manufacturers' => $manufacturerNames,
        ];
    }

    private function pdfTableColumns(bool $hasSupplierCatalog = false): array
    {
        $sideColumns = [
            ['label' => 'Pack', 'width' => 28],
            ['label' => 'Categories', 'width' => 62],
            ['label' => 'Visibility', 'width' => 34],
            ['label' => 'Exclusive', 'width' => 34],
            ['label' => 'Active', 'width' => 30],
            ['label' => 'Discount', 'width' => 38],
            ['label' => 'EOL', 'width' => 30],
        ];

        if ($hasSupplierCatalog) {
            $sideColumns[] = ['label' => 'Supplier %', 'width' => 46];
            $sideColumns[] = ['label' => 'Supplier', 'width' => 46];
        }

        $sideColumns[] = ['label' => 'Purchase', 'width' => 42];

        $rightColumns = [
            ['label' => 'Purchase', 'width' => 42],
        ];

        if ($hasSupplierCatalog) {
            $rightColumns[] = ['label' => 'Supplier', 'width' => 46];
            $rightColumns[] = ['label' => 'Supplier %', 'width' => 46];
        }

        $rightColumns = array_merge($rightColumns, [
            ['label' => 'EOL', 'width' => 30],
            ['label' => 'Discount', 'width' => 38],
            ['label' => 'Active', 'width' => 30],
            ['label' => 'Exclusive', 'width' => 34],
            ['label' => 'Visibility', 'width' => 34],
            ['label' => 'Categories', 'width' => 62],
            ['label' => 'Pack', 'width' => 28],
        ]);

        return array_merge($sideColumns, [['label' => 'Reference', 'width' => 64]], $rightColumns);
    }

    private function pdfStoreCells(?array $store, bool $exclusive, bool $mirrored = false, bool $hasSupplierCatalog = false, array $row = []): array
    {
        if (! $store) {
            return array_fill(0, $hasSupplierCatalog ? 10 : 8, ['text' => '-']);
        }

        $cells = [
            ['text' => $store['is_pack'] ? 'yes' : 'no', 'fill' => $store['is_pack'] ? [0.82, 0.91, 0.86] : [0.97, 0.84, 0.85]],
            ['text' => $store['category'] ?: '-'],
            ['text' => $store['visibility'] ?: '-'],
            ['text' => $exclusive ? 'yes' : 'no', 'fill' => $exclusive ? [1, 0.95, 0.80] : [0.89, 0.90, 0.91]],
            ['text' => $store['is_active'] ? 'yes' : 'no', 'fill' => $store['is_active'] ? [0.82, 0.91, 0.86] : [0.97, 0.84, 0.85]],
            [
                'text' => $store['discount'] ?: '-',
                'fill' => $store['discount_type'] === 'individual'
                    ? [0.97, 0.84, 0.85]
                    : ($store['discount_type'] === 'global' ? [0.81, 0.96, 0.99] : [1, 1, 1]),
                'bold' => $store['discount'] !== '',
            ],
            ['text' => $store['wm_deprecated'] ? 'yes' : 'no', 'fill' => $store['wm_deprecated'] ? [1, 0.95, 0.80] : [0.97, 0.84, 0.85]],
        ];

        if ($hasSupplierCatalog) {
            $cells[] = ['text' => ($row['supplier_catalog_discount'] ?? null) !== null ? number_format((float) $row['supplier_catalog_discount'], 2, '.', '') . '%' : '-'];
            $cells[] = ['text' => ($row['supplier_catalog_purchase'] ?? null) !== null ? number_format((float) $row['supplier_catalog_purchase'], 2, '.', '') : '-'];
        }

        $cells[] = ['text' => number_format((float) $store['purchase_price'], 2, '.', '')];

        return $mirrored ? array_reverse($cells) : $cells;
    }

    private function pdfFromPageContents(array $pages): string
    {
        $objects = [];
        $pageIds = [];
        $fontId = 3;
        $nextId = 4;

        foreach ($pages as $content) {
            $contentId = $nextId++;
            $pageId = $nextId++;
            $objects[$contentId] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
            $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 {$fontId} 0 R >> >> /Contents {$contentId} 0 R >>";
            $pageIds[] = $pageId;
        }

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', array_map(fn ($id) => $id . ' 0 R', $pageIds)) . '] /Count ' . count($pageIds) . ' >>';
        $objects[$fontId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        foreach (array_keys($objects) as $id) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$id]) . "\n";
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    private function pdfCell(float $x, float $y, float $width, float $height, string $text, array $fill, array $color, float $fontSize, bool $bold = false): string
    {
        $safeText = $this->pdfText($this->pdfTruncate($text, max(3, (int) floor($width / ($fontSize * 0.62)))));
        $textWidth = strlen($safeText) * $fontSize * 0.48;
        $textX = $x + max(2, ($width - $textWidth) / 2);
        $textY = $y + ($height / 2) - ($fontSize / 2) + 1;

        return sprintf(
            "q %.3F %.3F %.3F rg %.2F %.2F %.2F %.2F re f Q\n" .
            "q 0.82 0.85 0.88 RG %.2F %.2F %.2F %.2F re S Q\n" .
            "BT %.3F %.3F %.3F rg /F1 %.2F Tf %.2F %.2F Td (%s) Tj ET\n",
            $fill[0],
            $fill[1],
            $fill[2],
            $x,
            $y,
            $width,
            $height,
            $x,
            $y,
            $width,
            $height,
            $color[0],
            $color[1],
            $color[2],
            $fontSize,
            $textX,
            $textY,
            $safeText
        );
    }

    private function pdfTextAt(float $x, float $y, string $text, float $fontSize, array $color): string
    {
        return sprintf(
            "BT %.3F %.3F %.3F rg /F1 %.2F Tf %.2F %.2F Td (%s) Tj ET\n",
            $color[0],
            $color[1],
            $color[2],
            $fontSize,
            $x,
            $y,
            $this->pdfText($text)
        );
    }

    private function pdfTruncate(string $text, int $length): string
    {
        $text = trim($text);

        return strlen($text) > $length ? substr($text, 0, max(0, $length - 1)) . '.' : $text;
    }

    private function pdfText(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = substr((string) $text, 0, 180);

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function filenamePart(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/i', '_', $value) ?: 'all';

        return trim($value, '_') ?: 'all';
    }

    private function supplierCatalog(string $path): array
    {
        $handle = fopen($path, 'r');

        if (! $handle) {
            return [];
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return [];
        }

        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);

        fgetcsv($handle, 0, $delimiter);

        $referenceIndex = 0;
        $priceIndex = 1;
        $discountIndex = 2;

        $catalog = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $reference = trim((string) ($row[$referenceIndex] ?? ''));
            $price = $this->decimalValue((string) ($row[$priceIndex] ?? ''));
            $discount = $this->decimalValue((string) ($row[$discountIndex] ?? '')) ?? 0.0;

            if ($reference !== '' && $price !== null) {
                $catalog[$reference] = [
                    'reference' => $reference,
                    'price' => $price,
                    'discount' => $discount,
                    'net_price' => $price,
                ];
            }
        }

        fclose($handle);

        return $catalog;
    }

    private function applySupplierCatalog(array $rows, array $catalog): array
    {
        return array_map(function (array $row) use ($catalog) {
            $catalogItem = $catalog[$row['reference']] ?? null;
            $catalogPurchase = $catalogItem['net_price'] ?? null;
            $row['supplier_catalog_purchase'] = $catalogPurchase;
            $row['supplier_catalog_price'] = $catalogItem['price'] ?? null;
            $row['supplier_catalog_discount'] = $catalogItem['discount'] ?? null;
            $row['supplier_catalog_asm_diff'] = $catalogPurchase !== null && $row['asm']
                ? round($catalogPurchase - (float) $row['asm']['purchase_price'], 2)
                : null;
            $row['supplier_catalog_asd_diff'] = $catalogPurchase !== null && $row['asd']
                ? round($catalogPurchase - (float) $row['asd']['purchase_price'], 2)
                : null;

            return $row;
        }, $rows);
    }

    private function pendingSupplierImports(array $rows, array $catalog): array
    {
        $existingReferences = array_fill_keys(array_column($rows, 'reference'), true);

        return array_values(array_filter($catalog, function (array $catalogItem) use ($existingReferences) {
            return ! isset($existingReferences[$catalogItem['reference']]);
        }));
    }

    private function decimalValue(string $value): ?float
    {
        $value = trim(str_replace(["\xc2\xa0", ' '], '', $value));
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/[^0-9.\-]/', '', $value);

        return is_numeric($value) ? round((float) $value, 2) : null;
    }
}
