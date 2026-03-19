<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use App\Models\prestashop\manufacturers;
use App\Models\prestashop\product;

class PurchasePriceSyncService
{
    private $pushUrl;
    private $token;

    private $dataCode;
    private $notFoundTo;

    public function __construct(){
        $this->pushUrl = 'https://www.all-stars-distribution.com/custom/api/products/updateWholeSalePrice.php';
        $this->token   = '9f7b4c3c6d5a2b2a8e8e7e0c4f6f9e1d5d0e8b3c7a4f2a9d1c6b8e4a2f7c9d6';

        $this->dataCode   = 'asd_Products_Wholesale';
        $this->notFoundTo = config('purchase_prices.endpoint.notfound_to');
    }

    public function syncByManufacturerId($manufacturerId){
        if (is_string($manufacturerId) && strtoupper($manufacturerId) === 'ALL') return $this->syncAllBrands();
        return $this->syncOneBrand((int) $manufacturerId);
    }

    private function syncOneBrand($manufacturerId){
        $brand = $this->getManufacturerFromSource($manufacturerId);
        if (!$brand) throw new \RuntimeException('Manufacturer not found: ' . $manufacturerId);
        return $this->pushBrandPayload($brand);
    }

    private function syncAllBrands(){
        
        $brands = manufacturers::orderBy('name')->get();

        $summary = array(
            'mode' => 'ALL',
            'brands_total' => (int) $brands->count(),
            'brands_sent' => 0,
            'sent' => 0,
            'updated' => 0,
            'not_found' => 0,
            'brands' => array(),
            'errors' => array(),
        );

        foreach ($brands as $brand) {
            try {
                $result = $this->pushBrandPayload($brand);

                $summary['brands'][] = $result;

                $summary['brands_sent']++;
                $summary['sent']      += (int) (isset($result['sent']) ? $result['sent'] : 0);
                $summary['updated']   += (int) (isset($result['updated']) ? $result['updated'] : 0);
                $summary['not_found'] += (int) (isset($result['not_found']) ? $result['not_found'] : 0);

            } catch (\Exception $e) {
                $summary['errors'][] = array(
                    'manufacturer_id' => (int) $brand->id_manufacturer,
                    'brand' => (string) $brand->name,
                    'message' => $e->getMessage(),
                );

                Log::error('PurchasePriceSyncService ALL brand failed', array(
                    'manufacturer_id' => (int) $brand->id_manufacturer,
                    'brand' => (string) $brand->name,
                    'exception' => $e->getMessage(),
                ));
            }
        }

        return $summary;
    }

    private function pushBrandPayload($brand){
        
        $manufacturerId = (int) $brand->id_manufacturer;
        $currency = (string) $brand->currency;

        $wholesaleColumn = $this->resolveWholesaleColumn($currency);
        $products = $this->getProductsFromSource($manufacturerId, $wholesaleColumn);
        
        
        $items = array();
        foreach ($products as $p) {
            if (!isset($p->reference) || $p->reference === '') continue;

            $value = 0;
            if (isset($p->{$wholesaleColumn})) $value = (float) $p->{$wholesaleColumn};

            $items[] = array(
                'reference' => (string) $p->reference,
                'wholesale_price' => $value,   // normalizado
                'currency' => $currency,
            );
        }

        if (count($items) === 0) {
            return array(
                'error' => false,
                'manufacturer_id' => $manufacturerId,
                'brand' => (string) $brand->name,
                'currency' => $currency,
                'sent' => 0,
                'updated' => 0,
                'not_found' => 0,
                'not_found_refs' => array(),
                'message' => 'No references to sync for this manufacturer.',
            );
        }

        $payload = array(
            'dataCode' => $this->dataCode,
            'manufacturer_id' => $manufacturerId,
            'brand' => (string) $brand->name,
            'currency' => $currency,
            'items' => $items,
        );
        
        $headers = array('Content-Type' => 'application/json');
        if (!empty($this->token)) $headers['X-SYNC-TOKEN'] = $this->token;
        
        $response = Http::withHeaders($headers)->timeout(180)->post($this->pushUrl, $payload);

        if (!$response->ok()) throw new \RuntimeException('Target push failed: HTTP ' . $response->status() . ' - ' . $response->body());

        $result = $response->json();
        
        dd( $result );
        
        if (!is_array($result)) throw new \RuntimeException('Target returned invalid JSON.');

        if (isset($result['not_found_refs']) && is_array($result['not_found_refs']) && count($result['not_found_refs']) > 0) {
            $this->emailNotFound($result['not_found_refs'], $manufacturerId, (string) $brand->name);
        }

        dd($result);

        /**
        // acrescentar metadados úteis
        $result['manufacturer_id'] = $manufacturerId;
        $result['brand'] = (string) $brand->name;
        $result['currency'] = $currency;
        $result['sent'] = count($items);
        **/
        
        dd('FIM');
        return $result;
    }

    private function resolveWholesaleColumn($currency){
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

    private function getManufacturerFromSource($manufacturerId){
        return manufacturers::where('id_manufacturer', (int)$manufacturerId)->first();
    }

    private function getProductsFromSource($manufacturerId, $wholesaleColumn){
        return product::where('id_manufacturer', (int)$manufacturerId)->where('reference', '<>', '')->select(array('reference', $wholesaleColumn))->get();
    }

    private function emailNotFound(array $refs, $manufacturerId, $brandName){
        if (empty($this->notFoundTo)) {
            Log::warning('NotFound refs (no email configured)', array(
                'manufacturer_id' => (int)$manufacturerId,
                'brand' => (string)$brandName,
                'refs' => $refs,
            ));
            return;
        }

        $safeRefs = array();
        foreach ($refs as $r) {
            $safeRefs[] = htmlspecialchars((string)$r, ENT_QUOTES, 'UTF-8');
        }

        $html = 'Existem referências que não foram encontradas!<br><br>'
            . 'Manufacturer ID: ' . (int)$manufacturerId . '<br>'
            . 'Brand: ' . htmlspecialchars((string)$brandName, ENT_QUOTES, 'UTF-8') . '<br><br>'
            . implode('<br>', $safeRefs);

        $to = $this->notFoundTo;

        Mail::html($html, function ($message) use ($to) {
            $message->to($to)->subject('PURCHASE PRICE SYNC - not found');
        });
    }
}