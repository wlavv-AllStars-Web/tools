<?php

declare(strict_types=1);

namespace App\Services\Prestashop;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CustomerCartAccessService
{
    /**
     * Finds the store(s) in which this email is known. A customer can be linked
     * by its own id_shop or by previous carts/orders in a store.
     */
    public function storesForEmail(string $email): Collection
    {
        return collect(['ASM', 'ASD'])
            ->map(function (string $store) use ($email) {
                $customer = $this->customerForStore($email, $store);

                if (!$customer) {
                    return null;
                }

                return (object) [
                    'store' => $store,
                    'id_customer' => (int) $customer->id_customer,
                    'firstname' => (string) $customer->firstname,
                    'lastname' => (string) $customer->lastname,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Reuses the newest unconverted cart for the selected customer/store, or
     * creates a valid empty PrestaShop cart when no such cart exists.
     */
    public function createOrReuse(string $email, string $store): array
    {
        $store = $this->normalizeStore($store);
        $shopId = $this->shopId($store);
        $prefix = $this->prefix();
        $db = DB::connection('mysql2');

        $result = $db->transaction(function () use ($db, $prefix, $email, $store, $shopId) {
            $customer = $this->customerForStore($email, $store, true);

            if (!$customer) {
                throw ValidationException::withMessages([
                    'email' => 'O cliente não pertence à loja selecionada ou não está ativo.',
                ]);
            }

            $cart = $db->table($prefix.'cart as c')
                ->where('c.id_customer', (int) $customer->id_customer)
                ->where('c.id_shop', $shopId)
                ->whereNotExists(function ($query) use ($prefix) {
                    $query->selectRaw('1')
                        ->from($prefix.'orders as o')
                        ->whereColumn('o.id_cart', 'c.id_cart');
                })
                ->orderByDesc('c.date_upd')
                ->orderByDesc('c.id_cart')
                ->lockForUpdate()
                ->first(['c.id_cart']);

            $reused = $cart !== null;
            $cartId = $reused
                ? (int) $cart->id_cart
                : $this->createEmptyCart($db, $prefix, $customer, $shopId);

            return [
                'cart_id' => $cartId,
                'customer_id' => (int) $customer->id_customer,
                'customer_name' => trim((string) $customer->firstname.' '.(string) $customer->lastname),
                'reused' => $reused,
            ];
        });

        $result['store'] = $store;
        $result['url'] = $this->recoveryUrl($store, (int) $result['cart_id']);

        Log::info('Customer cart access created.', [
            'tools_user_id' => auth()->id(),
            'customer_id' => $result['customer_id'],
            'store' => $store,
            'cart_id' => $result['cart_id'],
            'reused_cart' => $result['reused'],
        ]);

        return $result;
    }

    private function customerForStore(string $email, string $store, bool $lock = false): ?object
    {
        $prefix = $this->prefix();
        $shopId = $this->shopId($store);
        $query = DB::connection('mysql2')
            ->table($prefix.'customer as customer')
            ->whereRaw('LOWER(customer.email) = ?', [mb_strtolower(trim($email))])
            ->where('customer.active', 1)
            ->where('customer.deleted', 0)
            ->where(function ($query) use ($prefix, $shopId) {
                $query->where('customer.id_shop', $shopId)
                    ->orWhereExists(function ($subQuery) use ($prefix, $shopId) {
                        $subQuery->selectRaw('1')
                            ->from($prefix.'cart as cart')
                            ->whereColumn('cart.id_customer', 'customer.id_customer')
                            ->where('cart.id_shop', $shopId);
                    })
                    ->orWhereExists(function ($subQuery) use ($prefix, $shopId) {
                        $subQuery->selectRaw('1')
                            ->from($prefix.'orders as orders')
                            ->whereColumn('orders.id_customer', 'customer.id_customer')
                            ->where('orders.id_shop', $shopId);
                    });
            })
            ->orderByDesc('customer.id_customer');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first([
            'customer.id_customer',
            'customer.firstname',
            'customer.lastname',
            'customer.secure_key',
        ]);
    }

    private function createEmptyCart($db, string $prefix, object $customer, int $shopId): int
    {
        $languageId = $this->shopConfigurationValue($db, $prefix, 'PS_LANG_DEFAULT', $shopId, 1);
        $currencyId = $this->shopConfigurationValue($db, $prefix, 'PS_CURRENCY_DEFAULT', $shopId, 2);
        $shopGroupId = (int) ($db->table($prefix.'shop')
            ->where('id_shop', $shopId)
            ->value('id_shop_group') ?: 1);
        $guestId = (int) ($db->table($prefix.'guest')
            ->where('id_customer', (int) $customer->id_customer)
            ->orderByDesc('id_guest')
            ->value('id_guest') ?: 0);

        if (empty($customer->secure_key)) {
            throw ValidationException::withMessages([
                'email' => 'O cliente não tem secure_key válido para criar um carrinho.',
            ]);
        }

        return (int) $db->table($prefix.'cart')->insertGetId([
            'id_shop_group' => $shopGroupId,
            'id_shop' => $shopId,
            'id_carrier' => 0,
            'id_lang' => $languageId,
            'id_address_delivery' => 0,
            'id_address_invoice' => 0,
            'id_currency' => $currencyId,
            'id_customer' => (int) $customer->id_customer,
            'id_guest' => $guestId,
            'secure_key' => (string) $customer->secure_key,
            'recyclable' => 0,
            'gift' => 0,
            'gift_message' => '',
            'mobile_theme' => 0,
            'allow_seperated_package' => 0,
            'date_add' => now(),
            'date_upd' => now(),
        ], 'id_cart');
    }

    private function recoveryUrl(string $store, int $cartId): string
    {
        $cookieKey = (string) config('prestashop.stores.'.$store.'.cookie_key');

        if ($cookieKey === '') {
            throw new \RuntimeException('The PrestaShop recovery-cart key is not configured for '.$store.'.');
        }

        $prefix = $this->prefix();
        $shopId = $this->shopId($store);
        $db = DB::connection('mysql2');
        $languageId = $this->shopConfigurationValue(
            $db,
            $prefix,
            'PS_LANG_DEFAULT',
            $shopId,
            1
        );
        $languageIso = (string) ($db->table($prefix.'lang')
            ->where('id_lang', $languageId)
            ->value('iso_code') ?: 'en');
        $shopUrl = $db->table($prefix.'shop_url')
            ->where('id_shop', $shopId)
            ->where('active', 1)
            ->orderByDesc('main')
            ->first(['domain_ssl', 'domain', 'physical_uri', 'virtual_uri']);
        $domain = trim((string) ($shopUrl->domain_ssl ?? $shopUrl->domain ?? ''));
        $path = trim((string) (($shopUrl->physical_uri ?? '').($shopUrl->virtual_uri ?? '')), '/');
        $baseUrl = $domain !== ''
            ? 'https://'.$domain.($path !== '' ? '/'.$path : '')
            : rtrim((string) config('allstars.stores.'.$store.'.base_url'), '/');

        if ($baseUrl === '') {
            throw new \RuntimeException('The storefront URL is not configured for '.$store.'.');
        }

        return $baseUrl.'/'.rawurlencode($languageIso).'/cart?'.http_build_query([
            'recover_cart' => $cartId,
            'token_cart' => md5($cookieKey.'recover_cart_'.$cartId),
        ]);
    }

    private function shopConfigurationValue($db, string $prefix, string $name, int $shopId, int $fallback): int
    {
        $value = $db->table($prefix.'configuration')
            ->where('name', $name)
            ->where(function ($query) use ($shopId) {
                $query->where('id_shop', $shopId)
                    ->orWhere('id_shop', 0)
                    ->orWhereNull('id_shop');
            })
            ->orderByRaw('CASE WHEN id_shop = ? THEN 0 ELSE 1 END', [$shopId])
            ->value('value');

        return is_numeric($value) && (int) $value > 0 ? (int) $value : $fallback;
    }

    private function shopId(string $store): int
    {
        return (int) config('allstars.stores.'.$store.'.id_shop');
    }

    private function prefix(): string
    {
        return (string) (env('DB2_prefix') ?: env('DB2_DB_prefix') ?: 'ps_');
    }

    private function normalizeStore(string $store): string
    {
        $store = strtoupper(trim($store));

        if (!in_array($store, ['ASM', 'ASD'], true)) {
            throw ValidationException::withMessages(['store' => 'Loja inválida.']);
        }

        return $store;
    }
}
