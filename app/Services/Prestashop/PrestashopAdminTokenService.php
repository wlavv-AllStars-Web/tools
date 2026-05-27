<?php

namespace App\Services\Prestashop;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PrestashopAdminTokenService
{
    public static function token(string $controller, string $store = 'ASD'): ?string
    {
        $employeeId = static::employeeId();

        if (!$employeeId) {
            return null;
        }

        $idTab = static::idTab($controller);

        if (!$idTab) {
            return null;
        }

        return static::generate($controller, (int) $idTab, (int) $employeeId, $store);
    }

    public static function tokenWithFreshTabLookup(string $controller, string $store = 'ASD'): ?string
    {
        $employeeId = static::employeeId();

        if (!$employeeId) {
            return null;
        }

        $idTab = static::idTab($controller);

        if (!$idTab) {
            static::clearIdTabCache($controller);
            $idTab = static::freshIdTab($controller);
        }

        if (!$idTab) {
            return null;
        }

        return static::generate($controller, (int) $idTab, (int) $employeeId, $store);
    }

    public static function tokenMd5(string $controller, string $store = 'ASD'): ?string
    {
        $employeeId = static::employeeId();

        if (!$employeeId) {
            return null;
        }

        $idTab = static::idTab($controller);

        if (!$idTab) {
            return null;
        }

        return static::generateMd5($controller, (int) $idTab, (int) $employeeId, $store);
    }

    public static function employeeId(): ?int
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        if (!empty($user->email)) {
            $prefix = static::prefix();
            $idEmployee = DB::connection('mysql2')
                ->table($prefix . 'employee')
                ->where('email', $user->email)
                ->value('id_employee');

            if ($idEmployee) {
                return (int) $idEmployee;
            }
        }

        return $user->id ? (int) $user->id : null;
    }

    public static function idTab(string $controller): ?int
    {
        return Cache::remember("prestashop.admin_token.id_tab.{$controller}", now()->addHours(6), function () use ($controller) {
            return static::freshIdTab($controller);
        });
    }

    public static function freshIdTab(string $controller): ?int
    {
        $prefix = static::prefix();
        $idTab = DB::connection('mysql2')
            ->table($prefix . 'tab')
            ->where('class_name', $controller)
            ->value('id_tab');

        return $idTab ? (int) $idTab : null;
    }

    public static function clearIdTabCache(string $controller): void
    {
        Cache::forget("prestashop.admin_token.id_tab.{$controller}");
    }

    public static function generate(string $controller, int $idTab, int $idEmployee, string $store = 'ASD'): string
    {
        $cookieKey = static::cookieKey($store);

        return hash('sha256', $cookieKey . $controller . (int) $idTab . (int) $idEmployee);
    }

    public static function generateMd5(string $controller, int $idTab, int $idEmployee, string $store = 'ASD'): string
    {
        $cookieKey = static::cookieKey($store);

        return md5($cookieKey . $controller . (int) $idTab . (int) $idEmployee);
    }

    public static function cookieKey(string $store = 'ASD'): string
    {
        $store = static::normalizeStore($store);

        return (string) (
            config("prestashop.stores.{$store}.cookie_key")
            ?: env('PRESTASHOP_' . $store . '_COOKIE_KEY')
            ?: env('PRESTASHOP_COOKIE_KEY')
        );
    }

    public static function prefix(): string
    {
        return env('DB2_DB_prefix', env('DB2_prefix', 'ps_'));
    }

    public static function normalizeStore(string $store): string
    {
        $store = strtoupper(trim($store));

        return in_array($store, ['ASM', 'ASD'], true) ? $store : 'ASD';
    }
}
