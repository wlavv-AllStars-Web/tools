<?php

namespace App\Services\Prestashop;

class PrestashopAdminLinkService
{
    public static function dashboardProductLink(string $field = 'id_product', string $store = 'ASM'): array
    {
        return static::dashboardLink('product', $field, $store);
    }

    public static function dashboardOrderLink(string $field = 'id_order', string $store = 'ASM'): array
    {
        return static::dashboardLink('order', $field, $store);
    }

    public static function dashboardCustomerLink(string $field = 'id_customer', string $store = 'ASM'): array
    {
        return static::dashboardLink('customer', $field, $store);
    }

    public static function dashboardCategoryLink(string $field = 'id_category', string $store = 'ASM'): array
    {
        return static::dashboardLink('category', $field, $store);
    }

    public static function dashboardSupplierLink(string $field = 'id_supplier', string $store = 'ASM'): array
    {
        return static::dashboardLink('supplier', $field, $store);
    }

    public static function dashboardManufacturerLink(string $field = 'id_manufacturer', string $store = 'ASM'): array
    {
        return static::dashboardLink('manufacturer', $field, $store);
    }

    public static function dashboardLink(string $entity, string $field, string $store = 'ASM'): array
    {
        return [
            'mode' => 'url',
            'entity' => $entity,
            'field' => $field,
            'store' => static::normalizeStore($store),
        ];
    }

    public static function dashboardProductAdminUrl(int $idProduct, string $store = 'ASM'): ?string
    {
        return static::legacyAdminUrl('AdminProducts', [
            'id_product' => $idProduct,
            'updateproduct' => 1,
        ], $store);
    }

    public static function dashboardOrderAdminUrl(int $idOrder, string $store = 'ASM'): ?string
    {
        return static::legacyAdminUrl('AdminOrders', [
            'id_order' => $idOrder,
            'vieworder' => 1,
        ], $store);
    }

    public static function dashboardCustomerAdminUrl(int $idCustomer, string $store = 'ASM'): ?string
    {
        return static::legacyAdminUrl('AdminCustomers', [
            'id_customer' => $idCustomer,
            'viewcustomer' => 1,
        ], $store);
    }

    public static function dashboardCategoryAdminUrl(int $idCategory, string $store = 'ASM'): ?string
    {
        return static::legacyAdminUrl('AdminCategories', [
            'id_category' => $idCategory,
            'updatecategory' => 1,
        ], $store);
    }

    public static function dashboardSupplierAdminUrl(int $idSupplier, string $store = 'ASM'): ?string
    {
        return static::legacyAdminUrl('AdminSuppliers', [
            'id_supplier' => $idSupplier,
            'updatesupplier' => 1,
        ], $store);
    }

    public static function dashboardManufacturerAdminUrl(int $idManufacturer, string $store = 'ASM'): ?string
    {
        return static::legacyAdminUrl('AdminManufacturers', [
            'id_manufacturer' => $idManufacturer,
            'updatemanufacturer' => 1,
        ], $store);
    }

    public static function bridgeAdminUrl(string $targetController, array $targetParams = [], string $store = 'ASM'): ?string
    {
        $store = static::normalizeStore($store);

        $baseUrl = static::storeBaseUrl($store);
        $adminFolder = static::adminFolder($store);
        $bridgeToken = static::bridgeToken($store);

        if (!$baseUrl || !$adminFolder || !$bridgeToken) {
            return null;
        }

        $targetParams = base64_encode(http_build_query($targetParams));
        $params = [
            'controller' => 'AdminLsgwebtoolsbridgeRedirect',
            static::bridgeTokenParameter($store) => $bridgeToken,
            'target_controller' => $targetController,
            'target_params' => $targetParams,
        ];

        if ($employeeId = static::employeeId()) {
            $params['id_employee'] = $employeeId;
        }

        if (static::bridgeUsesHmac($store)) {
            $timestamp = time();
            $params['bridge_ts'] = $timestamp;
            $params['bridge_signature'] = hash_hmac(
                'sha256',
                $targetController . '|' . $targetParams . '|' . $timestamp,
                static::bridgeHmacSecret($store)
            );
        }

        return rtrim($baseUrl, '/') . '/' . trim($adminFolder, '/') . '/index.php?' . http_build_query($params);
    }

    public static function legacyAdminUrl(string $controller, array $params = [], string $store = 'ASM'): ?string
    {
        $store = static::normalizeStore($store);

        $baseUrl = static::storeBaseUrl($store);
        $adminFolder = static::adminFolder($store);
        $token = PrestashopAdminTokenService::token($controller, $store);

        if (!$baseUrl || !$adminFolder || !$token) {
            return null;
        }

        return rtrim($baseUrl, '/') . '/' . trim($adminFolder, '/') . '/index.php?' . http_build_query(array_merge([
            'controller' => $controller,
            'token' => $token,
        ], static::shopContextParams($store), $params));
    }

    public static function dashboardBridgeUrl(string $entity, int $id, string $store = 'ASM'): ?string
    {
        return match (strtolower($entity)) {
            'product', 'products' => static::dashboardProductAdminUrl($id, $store),
            'order', 'orders' => static::dashboardOrderAdminUrl($id, $store),
            'customer', 'customers' => static::dashboardCustomerAdminUrl($id, $store),
            'category', 'categories' => static::dashboardCategoryAdminUrl($id, $store),
            'supplier', 'suppliers' => static::dashboardSupplierAdminUrl($id, $store),
            'manufacturer', 'manufacturers', 'brand', 'brands' => static::dashboardManufacturerAdminUrl($id, $store),
            default => null,
        };
    }

    public static function dashboardUrl(string $entity, int $id, string $store = 'ASM'): ?string
    {
        return static::dashboardBridgeUrl($entity, $id, $store);
    }

    public static function normalizeStore(string $store): string
    {
        $store = strtoupper(trim($store));

        return in_array($store, ['ASM', 'ASD'], true) ? $store : 'ASM';
    }

    public static function storeBaseUrl(string $store = 'ASM'): ?string
    {
        $store = static::normalizeStore($store);

        $configured = config("allstars.stores.{$store}.base_url");

        return $configured ? rtrim((string) $configured, '/') : null;
    }

    public static function shopId(string $store = 'ASM'): ?int
    {
        $store = static::normalizeStore($store);
        $idShop = config("allstars.stores.{$store}.id_shop");

        return $idShop ? (int) $idShop : null;
    }

    public static function shopContextParams(string $store = 'ASM'): array
    {
        $idShop = static::shopId($store);

        return $idShop ? ['setShopContext' => 's-' . $idShop] : [];
    }

    public static function adminFolder(string $store = 'ASM'): string
    {
        $store = static::normalizeStore($store);

        return (string) (
            config("allstars.stores.{$store}.admin_folder")
            ?: env('PRESTASHOP_' . $store . '_ADMIN_FOLDER', 'admineuromus1')
        );
    }

    public static function bridgeToken(string $store = 'ASM'): ?string
    {
        $store = static::normalizeStore($store);
        $token = config("prestashop.stores.{$store}.bridge_token");

        return $token ? (string) $token : null;
    }

    public static function bridgeTokenParameter(string $store = 'ASM'): string
    {
        $store = static::normalizeStore($store);
        $parameter = trim((string) config("prestashop.stores.{$store}.bridge_token_parameter", 'bridge_key'));

        return $parameter !== '' ? $parameter : 'bridge_key';
    }

    public static function employeeId(): ?int
    {
        $id = auth()->id();

        return $id ? (int) $id : null;
    }

    public static function bridgeUsesHmac(string $store = 'ASM'): bool
    {
        $store = static::normalizeStore($store);

        return (bool) config("prestashop.stores.{$store}.bridge_use_hmac", false);
    }

    public static function bridgeHmacSecret(string $store = 'ASM'): string
    {
        $store = static::normalizeStore($store);

        return (string) config("prestashop.stores.{$store}.bridge_hmac_secret", '');
    }

    public function product(int $idProduct, string $store = 'ASM'): ?string
    {
        return static::dashboardProductAdminUrl($idProduct, $store);
    }

    public function order(int $idOrder, string $store = 'ASM'): ?string
    {
        return static::dashboardOrderAdminUrl($idOrder, $store);
    }

    public function customer(int $idCustomer, string $store = 'ASM'): ?string
    {
        return static::dashboardCustomerAdminUrl($idCustomer, $store);
    }

    public function link(string $entity, int $id, string $store = 'ASM'): ?string
    {
        return static::dashboardBridgeUrl($entity, $id, $store);
    }
    
    public static function dashboardReviewsUrl(string $store = 'ASM'): ?string
    {
        return self::legacyAdminUrl(
            'AdminModulesSf',
            [
                'configure'   => 'productcomments',
                'tab_module'  => 'front_office_features',
                'module_name' => 'productcomments',
            ],
            $store
        );
    }

    public static function moduleConfigureUrl(string $moduleName, string $store = 'ASM'): ?string
    {
        $store = static::normalizeStore($store);

        $baseUrl = static::storeBaseUrl($store);
        $adminFolder = static::adminFolder($store);

        if (!$baseUrl || !$adminFolder) {
            return null;
        }

        return rtrim($baseUrl, '/')
            . '/' . trim($adminFolder, '/')
            . '/index.php/improve/modules/manage/action/configure/'
            . rawurlencode($moduleName);
    }
}
