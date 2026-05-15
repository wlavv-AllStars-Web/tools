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
        ], $params));
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

    public static function adminFolder(string $store = 'ASM'): string
    {
        $store = static::normalizeStore($store);

        return (string) (
            config("allstars.stores.{$store}.admin_folder")
            ?: env('PRESTASHOP_' . $store . '_ADMIN_FOLDER', 'admineuromus1')
        );
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
}
