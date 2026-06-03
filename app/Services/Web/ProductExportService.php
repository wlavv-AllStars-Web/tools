<?php

namespace App\Services\Web;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductExportService
{
    private const DELIMITER = ';';

    public function export(?string $filename = null): array
    {
        $directory = $this->directory();

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('Unable to create product export directory.');
        }

        $filename ??= 'products_export_' . now()->format('Ymd_His') . '.csv';
        $path = $directory . DIRECTORY_SEPARATOR . $filename;
        $handle = fopen($path, 'w');

        if ($handle === false) {
            throw new RuntimeException('Unable to open product export file.');
        }

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $this->headers(), self::DELIMITER);

        $rows = $this->rows();

        foreach ($rows as $row) {
            fputcsv($handle, [
                $row->reference,
                $row->id_product,
                $row->id_product_attribute,
                (int) $row->quantity,
                number_format((float) $row->wholesale_price_eur, 2, '.', ''),
                number_format((float) $row->wholesale_price_base_currency, 2, '.', ''),
                $row->active,
                $row->visibility,
                $row->manufacturer,
                $row->supplier,
            ], self::DELIMITER);
        }

        fclose($handle);

        return [
            'filename' => $filename,
            'path' => $path,
            'rows' => $rows->count(),
        ];
    }

    public function files(): Collection
    {
        $directory = $this->directory();

        if (! is_dir($directory)) {
            return collect();
        }

        return collect(glob($directory . DIRECTORY_SEPARATOR . '*.csv') ?: [])
            ->map(fn (string $path) => [
                'filename' => basename($path),
                'path' => $path,
                'size' => filesize($path) ?: 0,
                'modified_at' => filemtime($path) ?: 0,
            ])
            ->sortByDesc('modified_at')
            ->values();
    }

    public function read(?string $filename = null): array
    {
        $file = $filename
            ? $this->pathFor($filename)
            : ($this->files()->first()['path'] ?? null);

        if (! $file || ! is_file($file)) {
            return [
                'filename' => null,
                'headers' => $this->headers(),
                'rows' => collect(),
            ];
        }

        $handle = fopen($file, 'r');

        if ($handle === false) {
            throw new RuntimeException('Unable to read product export file.');
        }

        $fileHeaders = fgetcsv($handle, 0, self::DELIMITER) ?: $this->headers();
        $fileHeaders = array_map(fn ($header) => ltrim((string) $header, "\xEF\xBB\xBF"), $fileHeaders);
        $headers = collect($this->headers())
            ->merge(collect($fileHeaders)->reject(fn ($header) => in_array($header, $this->headers(), true)))
            ->values()
            ->all();
        $rows = collect();

        while (($data = fgetcsv($handle, 0, self::DELIMITER)) !== false) {
            if ($this->emptyCsvRow($data)) {
                continue;
            }

            $row = array_combine($fileHeaders, array_pad($data, count($fileHeaders), ''));

            $rows->push(collect($headers)
                ->mapWithKeys(fn ($header) => [$header => $row[$header] ?? ''])
                ->all());
        }

        fclose($handle);

        return [
            'filename' => basename($file),
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    public function pathFor(string $filename): string
    {
        return $this->directory() . DIRECTORY_SEPARATOR . basename($filename);
    }

    private function rows(): Collection
    {
        $db = DB::connection('mysql2');
        $stock = $db->table('ps_stock_available')
            ->selectRaw('id_product, id_product_attribute, SUM(quantity) as quantity')
            ->groupBy('id_product', 'id_product_attribute');

        $productsWithoutAttributes = $db->table('ps_product as p')
            ->leftJoin('ps_manufacturer as m', 'm.id_manufacturer', '=', 'p.id_manufacturer')
            ->leftJoin('ps_supplier as s', 's.id_supplier', '=', 'p.id_supplier')
            ->leftJoinSub($stock, 'sa', function ($join) {
                $join->on('sa.id_product', '=', 'p.id_product')
                    ->where('sa.id_product_attribute', 0);
            })
            ->leftJoin('ps_custom_product as cp', 'cp.id_product', '=', 'p.id_product')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('ps_product_attribute as pa_check')
                    ->whereColumn('pa_check.id_product', 'p.id_product');
            })
            ->selectRaw("
                p.id_product,
                0 as id_product_attribute,
                p.reference as reference,
                COALESCE(sa.quantity, 0) as quantity,
                COALESCE(p.wholesale_price, 0) as wholesale_price_eur,
                COALESCE(cp.wholesale_price_base_currency, 0) as wholesale_price_base_currency,
                p.active,
                p.visibility,
                COALESCE(m.name, '') as manufacturer,
                COALESCE(s.name, '') as supplier
            ");

        $productsWithAttributes = $db->table('ps_product as p')
            ->join('ps_product_attribute as pa', 'pa.id_product', '=', 'p.id_product')
            ->leftJoin('ps_manufacturer as m', 'm.id_manufacturer', '=', 'p.id_manufacturer')
            ->leftJoin('ps_supplier as s', 's.id_supplier', '=', 'p.id_supplier')
            ->leftJoinSub($stock, 'sa', function ($join) {
                $join->on('sa.id_product', '=', 'p.id_product')
                    ->on('sa.id_product_attribute', '=', 'pa.id_product_attribute');
            })
            ->leftJoin('ps_custom_product as cp', 'cp.id_product', '=', 'p.id_product')
            ->leftJoin('ps_custom_product_attribute as cpa', function ($join) {
                $join->on('cpa.id_product', '=', 'p.id_product')
                    ->on('cpa.id_product_attribute', '=', 'pa.id_product_attribute');
            })
            ->selectRaw("
                p.id_product,
                pa.id_product_attribute,
                COALESCE(NULLIF(pa.reference, ''), p.reference) as reference,
                COALESCE(sa.quantity, 0) as quantity,
                COALESCE(NULLIF(pa.wholesale_price, 0), p.wholesale_price, 0) as wholesale_price_eur,
                COALESCE(cpa.wholesale_price_base_currency, cp.wholesale_price_base_currency, 0) as wholesale_price_base_currency,
                p.active,
                p.visibility,
                COALESCE(m.name, '') as manufacturer,
                COALESCE(s.name, '') as supplier
            ");

        return $productsWithoutAttributes
            ->unionAll($productsWithAttributes)
            ->orderBy('reference')
            ->get()
            ->groupBy(fn ($row) => trim((string) $row->reference))
            ->map(function (Collection $rows, string $reference) {
                return (object) [
                    'reference' => $reference,
                    'id_product' => $rows
                        ->pluck('id_product')
                        ->map(fn ($id) => (int) $id)
                        ->unique()
                        ->implode(','),
                    'id_product_attribute' => $rows
                        ->pluck('id_product_attribute')
                        ->map(fn ($id) => (int) $id)
                        ->filter(fn ($id) => $id > 0)
                        ->unique()
                        ->implode(','),
                    'quantity' => $rows->sum(fn ($row) => (int) $row->quantity),
                    'wholesale_price_eur' => (float) (
                        $rows
                            ->pluck('wholesale_price_eur')
                            ->first(fn ($value) => (float) $value > 0)
                        ?? 0
                    ),
                    'wholesale_price_base_currency' => (float) (
                        $rows
                            ->pluck('wholesale_price_base_currency')
                            ->first(fn ($value) => (float) $value > 0)
                        ?? 0
                    ),
                    'active' => $rows->contains(fn ($row) => (int) $row->active === 1) ? 1 : 0,
                    'visibility' => $rows
                        ->pluck('visibility')
                        ->filter()
                        ->unique()
                        ->implode(','),
                    'manufacturer' => $rows
                        ->pluck('manufacturer')
                        ->filter()
                        ->unique()
                        ->implode(','),
                    'supplier' => $rows
                        ->pluck('supplier')
                        ->filter()
                        ->unique()
                        ->implode(','),
                ];
            })
            ->sortBy('reference', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    private function headers(): array
    {
        return [
            'reference',
            'id_product',
            'id_product_attribute',
            'quantity',
            'wholesale_price_eur',
            'wholesale_price_base_currency',
            'active',
            'visibility',
            'manufacturer',
            'supplier',
        ];
    }

    private function directory(): string
    {
        return storage_path('app/web/product_exports');
    }

    private function emptyCsvRow(array $row): bool
    {
        return collect($row)->every(fn ($value) => trim((string) $value) === '');
    }
}
