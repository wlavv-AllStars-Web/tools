<?php

namespace App\Services;

use App\Models\prestashop\manufacturers;
use App\Services\Mail\StoreMailer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchasePriceSyncService
{
    private $dataCode;
    private $notFoundTo;

    public function __construct()
    {
        $this->dataCode = 'asd_Products_Wholesale';
        $this->notFoundTo = config('allstars.emails.purchase_price_sync_not_found_to', 'bruno.fernandes.asm@gmail.com');
    }

    public function syncByManufacturerId($manufacturerId)
    {
        if (is_string($manufacturerId) && strtoupper($manufacturerId) === 'ALL') {
            return $this->syncAllBrands();
        }

        return $this->syncOneBrand((int) $manufacturerId);
    }

    private function syncOneBrand($manufacturerId)
    {
        $brand = $this->getManufacturerFromSource($manufacturerId);

        if (!$brand) {
            throw new \RuntimeException('Manufacturer not found: ' . $manufacturerId);
        }

        return $this->pushBrandPayload($brand);
    }

    private function syncAllBrands()
    {
        $brands = manufacturers::orderBy('name')->get();

        $summary = [
            'mode' => 'ALL',
            'brands_total' => (int) $brands->count(),
            'brands_sent' => 0,
            'sent' => 0,
            'updated' => 0,
            'not_found' => 0,
            'brands' => [],
            'errors' => [],
        ];

        foreach ($brands as $brand) {
            try {
                $result = $this->pushBrandPayload($brand);
                $summary['brands'][] = $result;
                $summary['brands_sent']++;
                $summary['sent'] += (int) ($result['sent'] ?? 0);
                $summary['updated'] += (int) ($result['updated'] ?? 0);
                $summary['not_found'] += (int) ($result['not_found'] ?? 0);
            } catch (\Exception $e) {
                $summary['errors'][] = [
                    'manufacturer_id' => (int) $brand->id_manufacturer,
                    'brand' => (string) $brand->name,
                    'message' => $e->getMessage(),
                ];

                Log::error('PurchasePriceSyncService ALL brand failed', [
                    'manufacturer_id' => (int) $brand->id_manufacturer,
                    'brand' => (string) $brand->name,
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        return $summary;
    }

    private function pushBrandPayload($brand)
    {
        $manufacturerId = (int) $brand->id_manufacturer;
        $currency = (string) $brand->currency;
        $wholesaleColumn = $this->resolveWholesaleColumn($currency);
        $items = $this->getProductsFromSource($manufacturerId, $wholesaleColumn);

        if (count($items) === 0) {
            return [
                'error' => false,
                'manufacturer_id' => $manufacturerId,
                'brand' => (string) $brand->name,
                'currency' => $currency,
                'sent' => 0,
                'updated' => 0,
                'not_found' => 0,
                'not_found_refs' => [],
                'message' => 'No products to sync for this manufacturer.',
            ];
        }

        $updated = $this->syncItemsToShop($items, 3);

        return [
            'error' => false,
            'dataCode' => $this->dataCode,
            'manufacturer_id' => $manufacturerId,
            'brand' => (string) $brand->name,
            'currency' => $currency,
            'sent' => count($items),
            'updated' => $updated,
            'not_found' => 0,
            'not_found_refs' => [],
        ];
    }

    private function resolveWholesaleColumn($currency)
    {
        switch ((string) $currency) {
            case 'USD':
                return 'wholesale_price_dollar';
            case 'GBP':
                return 'wholesale_price_pound';
            case 'YEN':
                return 'wholesale_price_yen';
            case 'EUR':
            default:
                return 'wholesale_price';
        }
    }

    private function getManufacturerFromSource($manufacturerId)
    {
        return manufacturers::where('id_manufacturer', (int) $manufacturerId)->first();
    }

    private function getProductsFromSource($manufacturerId, $wholesaleColumn)
    {
        $prefix = env('DB2_DB_prefix', env('DB2_prefix', 'ps_'));

        return DB::connection('mysql2')
            ->table($prefix . 'product as p')
            ->leftJoin($prefix . 'product_attribute as pa', 'pa.id_product', '=', 'p.id_product')
            ->where('p.id_manufacturer', (int) $manufacturerId)
            ->select([
                'p.id_product',
                DB::raw('COALESCE(pa.id_product_attribute, 0) as id_product_attribute'),
                DB::raw('COALESCE(pa.reference, p.reference) as reference'),
                DB::raw('CASE WHEN COALESCE(pa.id_product_attribute, 0) > 0 AND COALESCE(pa.wholesale_price, 0) > 0 THEN pa.wholesale_price ELSE COALESCE(p.' . $wholesaleColumn . ', 0) END as wholesale_price'),
            ])
            ->orderBy('p.id_product')
            ->orderBy('pa.id_product_attribute')
            ->get()
            ->map(fn ($item) => [
                'id_product' => (int) $item->id_product,
                'id_product_attribute' => (int) $item->id_product_attribute,
                'reference' => (string) $item->reference,
                'wholesale_price' => (float) $item->wholesale_price,
            ])
            ->all();
    }

    private function syncItemsToShop(array $items, int $shopId): int
    {
        $prefix = env('DB2_DB_prefix', env('DB2_prefix', 'ps_'));
        $updated = 0;

        foreach ($items as $item) {
            if ((float) $item['wholesale_price'] <= 0) {
                continue;
            }

            if ((int) $item['id_product_attribute'] > 0) {
                $updated += DB::connection('mysql2')
                    ->table($prefix . 'product_attribute_shop')
                    ->where('id_product', (int) $item['id_product'])
                    ->where('id_product_attribute', (int) $item['id_product_attribute'])
                    ->where('id_shop', $shopId)
                    ->update(['wholesale_price' => (float) $item['wholesale_price']]);

                continue;
            }

            $updated += DB::connection('mysql2')
                ->table($prefix . 'product_shop')
                ->where('id_product', (int) $item['id_product'])
                ->where('id_shop', $shopId)
                ->update(['wholesale_price' => (float) $item['wholesale_price']]);
        }

        return $updated;
    }

    private function emailNotFound(array $refs, $manufacturerId, $brandName)
    {
        if (empty($this->notFoundTo)) {
            Log::warning('NotFound refs (no email configured)', [
                'manufacturer_id' => (int) $manufacturerId,
                'brand' => (string) $brandName,
                'refs' => $refs,
            ]);

            return;
        }

        $safeRefs = [];
        foreach ($refs as $r) {
            $safeRefs[] = htmlspecialchars((string) $r, ENT_QUOTES, 'UTF-8');
        }

        $html = 'Existem referencias que nao foram encontradas!<br><br>'
            . 'Manufacturer ID: ' . (int) $manufacturerId . '<br>'
            . 'Brand: ' . htmlspecialchars((string) $brandName, ENT_QUOTES, 'UTF-8') . '<br><br>'
            . implode('<br>', $safeRefs);

        $to = $this->notFoundTo;

        StoreMailer::sendHtml('asd_sales', $to, 'PURCHASE PRICE SYNC - not found', $html);
    }
}
