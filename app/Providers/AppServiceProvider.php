<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    
    protected $prestashop;

    public function register(): void{ }
    
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $data = $view->getData();

            if (isset($data['breadcrumbs'])) {
                return;
            }

            $breadcrumbs = $this->fallbackBreadcrumbs();

            if (!empty($breadcrumbs)) {
                $view->with('breadcrumbs', $breadcrumbs);
            }
        });
    }

    private function fallbackBreadcrumbs(): array
    {
        if (!request()->route()) {
            return [];
        }

        $routeName = (string) request()->route()->getName();

        if (str_starts_with($routeName, 'erp.oms.')) {
            if (str_starts_with($routeName, 'erp.oms.logistic_containers.')) {
                return $this->areaBreadcrumbs('administration', 'administration.index', 'OMS - logistic containers');
            }

            if (str_starts_with($routeName, 'erp.oms.supplier_terms.')) {
                return $this->areaBreadcrumbs('administration', 'administration.index', 'OMS - supplier terms');
            }

            return [
                ['name' => 'administration', 'url' => route('administration.index')],
                ['name' => 'OMS', 'url' => route('erp.oms.dashboard'), 'no_translation' => 1],
                ['name' => $this->routeTailLabel($routeName, 'erp.oms.'), 'url' => url()->current(), 'no_translation' => 1],
            ];
        }

        if ($routeName === 'erp.index') {
            return $this->areaBreadcrumbs('purchase', 'purchase.index', 'ERP');
        }

        if (str_starts_with($routeName, 'marketing.homepage.')) {
            return [
                ['name' => 'marketing', 'url' => route('marketing.index')],
                ['name' => 'ASM homepage', 'url' => route('marketing.homepage.index'), 'no_translation' => 1],
            ];
        }

        if (str_starts_with($routeName, 'marketing.homepage_ASD.')) {
            return [
                ['name' => 'marketing', 'url' => route('marketing.index')],
                ['name' => 'ASD homepage', 'url' => route('marketing.homepage_ASD.index'), 'no_translation' => 1],
            ];
        }

        if (str_starts_with($routeName, 'tasks.')) {
            return [
                ['name' => 'administration', 'url' => route('administration.index')],
                ['name' => 'Tasks', 'url' => url()->current(), 'no_translation' => 1],
            ];
        }

        foreach ($this->fallbackRoutePrefixMap() as $prefix => $config) {
            if (str_starts_with($routeName, $prefix)) {
                return $this->areaBreadcrumbs(...$config);
            }
        }

        return match ($routeName) {
            'translation.index' => [
                ['name' => 'web', 'url' => route('web.index')],
                ['name' => 'Tracking', 'url' => route('translation.index'), 'no_translation' => 1],
            ],
            'asg_tasks.index' => [
                ['name' => 'administration', 'url' => route('administration.index')],
                ['name' => 'Tasks', 'url' => route('asg_tasks.index'), 'no_translation' => 1],
            ],
            'carrierIssues.index' => [
                ['name' => 'logistics', 'url' => route('logistics.index')],
                ['name' => 'Carrier issues', 'url' => route('carrierIssues.index'), 'no_translation' => 1],
            ],
            'suppliersIssues.index' => [
                ['name' => 'purchase', 'url' => route('purchase.index')],
                ['name' => 'Supplier issues', 'url' => url()->current(), 'no_translation' => 1],
            ],
            'finance.download_intrastat',
            'finance.download_intrastat_import',
            'finance.download_intrastat_export',
            'finance.save_currency_rate' => $this->areaBreadcrumbs('finance', 'finance.index', 'Intrastat'),
            'stats.daily_stats' => $this->areaBreadcrumbs('dashboard', 'dashboard.index', 'Daily'),
            'stats.kpi' => $this->areaBreadcrumbs('dashboard', 'dashboard.index', 'KPI'),
            'logs.index',
            'logs.show' => $this->areaBreadcrumbs('web', 'web.index', 'Logs'),
            'compats.index' => $this->areaBreadcrumbs('administration', 'administration.index', 'Compats'),
            'site-text-side-by-side.index',
            'site-text-side-by-side.compare' => $this->areaBreadcrumbs('web', 'web.index', 'Raw text'),
            'site-seo-compare.index',
            'site-seo-compare.compare' => $this->areaBreadcrumbs('web', 'web.index', 'SEO'),
            'suppliersMap.index' => $this->areaBreadcrumbs('purchase', 'purchase.index', "Supplier's map"),
            'priceMap.index' => $this->areaBreadcrumbs('purchase', 'purchase.index', 'Price map'),
            'backorders.index' => $this->areaBreadcrumbs('sales', 'sales.index', 'Backorders'),
            'productIssues.index' => $this->areaBreadcrumbs('sales', 'sales.index', 'Product issues'),
            'returns.index' => $this->areaBreadcrumbs('sales', 'sales.index', 'Returns'),
            'warranties.index' => $this->areaBreadcrumbs('sales', 'sales.index', 'Warranties'),
            'checklist.index' => $this->areaBreadcrumbs('checklist', 'checklist.index', 'Manager'),
            'checklist.today' => $this->areaBreadcrumbs('checklist', 'checklist.index', 'Today'),
            'documentsManager.index' => $this->areaBreadcrumbs('documents', 'documentsManager.index', 'Document manager'),
            'quotes.index' => $this->quotesBreadcrumbs(),
            default => [],
        };
    }

    private function fallbackRoutePrefixMap(): array
    {
        return [
            'customTools.changesTracker.' => ['web', 'web.index', 'Changes'],
            'customTools.shipments.' => ['logistics', 'logistics.index', 'Carrier check'],
            'customTools.safety.' => ['logistics', 'logistics.index', 'Safety check'],
            'picking.' => ['logistics', 'logistics.index', 'Picking'],
            'housing.' => ['logistics', 'logistics.index', 'Housing'],
            'stockEntry.' => ['logistics', 'logistics.index', 'Stock entry'],
            'autoOrders.' => ['purchase', 'purchase.index', 'Auto orders'],
            'suppliersBackorders.' => ['purchase', 'purchase.index', "Supplier's backorders"],
            'marketing.resources.' => ['marketing', 'marketing.index', 'ASM resources'],
            'data.resources.' => ['marketing', 'marketing.index', 'ASD resources'],
            'asg_cars.' => ['marketing', 'marketing.index', 'Car gallery'],
            'tv.' => ['marketing', 'marketing.index', 'TV'],
            'refund.' => ['finance', 'finance.index', 'Refunds'],
            'carrierReturn.' => ['finance', 'finance.index', 'Carrier check'],
            'dashboard.tools.daily' => ['dashboard', 'dashboard.index', 'Daily'],
            'dashboard.tools.kpi' => ['dashboard', 'dashboard.index', 'KPI'],
            'dashboard.tools.changes' => ['dashboard', 'dashboard.index', 'Changes'],
            'web.tools.tracking.' => ['web', 'web.index', 'Tracking'],
            'web.tools.seo.' => ['web', 'web.index', 'SEO'],
            'web.tools.raw_text.' => ['web', 'web.index', 'Raw text'],
            'web.tools.changes.' => ['web', 'web.index', 'Changes'],
            'finance.tools.intrastat.' => ['finance', 'finance.index', 'Intrastat'],
            'finance.tools.carrier_check.' => ['finance', 'finance.index', 'Carrier check'],
            'finance.tools.carrier_returns.' => ['finance', 'finance.index', "Carrier returns"],
            'finance.tools.refunds.' => ['finance', 'finance.index', 'Refunds'],
            'finance.tools.vat.' => ['finance', 'finance.index', 'VAT'],
            'finance.tools.payment_links.' => ['finance', 'finance.index', "Payment link request's"],
            'logistics.tools.stats.' => ['logistics', 'logistics.index', 'Stats'],
            'logistics.tools.shipping.' => ['logistics', 'logistics.index', 'Shipping'],
            'logistics.tools.carrier_check.' => ['logistics', 'logistics.index', 'Carrier check'],
            'logistics.tools.shipments_check.' => ['logistics', 'logistics.index', 'Shipments check'],
            'logistics.tools.picking.' => ['logistics', 'logistics.index', 'Picking'],
            'logistics.tools.housing.' => ['logistics', 'logistics.index', 'Housing'],
            'logistics.tools.stock_entry.' => ['logistics', 'logistics.index', 'Stock entry'],
            'logistics.tools.stockEntry.' => ['logistics', 'logistics.index', 'Stock entry'],
            'logistics.tools.safety_check.' => ['logistics', 'logistics.index', 'Safety check'],
            'logistics.tools.carrier_issues.' => ['logistics', 'logistics.index', 'Carrier issues'],
            'logistics.tools.suppliers.issues.' => ['logistics', 'logistics.index', "Supplier's issues"],
            'logistics.tools.oms.' => ['logistics', 'logistics.index', 'OMS - logistic containers'],
            'admin.tools.asg_tasks.' => ['administration', 'administration.index', 'Tasks'],
            'admin.tools.tasks.' => ['administration', 'administration.index', 'Tasks'],
            'admin.tools.oms.' => ['administration', 'administration.index', 'OMS'],
            'admin.tools.compats.' => ['administration', 'administration.index', 'Compats'],
            'admin.tools.asd_alerts.' => ['administration', 'administration.index', 'ASD - Alerts'],
            'marketing.tools.tv.' => ['marketing', 'marketing.index', 'TV'],
            'marketing.tools.homepage.asm.' => ['marketing', 'marketing.index', 'ASM homepage'],
            'marketing.tools.homepage.asd.' => ['marketing', 'marketing.index', 'ASD homepage'],
            'marketing.tools.resources.asm.' => ['marketing', 'marketing.index', 'ASM resources'],
            'marketing.tools.resources.asd.' => ['marketing', 'marketing.index', 'ASD resources'],
            'marketing.tools.car_gallery.' => ['marketing', 'marketing.index', 'Car gallery'],
            'backoffice.tools.auto_orders.' => ['purchase', 'purchase.index', 'Auto orders'],
            'backoffice.tools.suppliers.map.' => ['purchase', 'purchase.index', "Supplier's map"],
            'backoffice.tools.price_map.' => ['purchase', 'purchase.index', 'Price map'],
            'backoffice.tools.suppliers.issues.' => ['purchase', 'purchase.index', "Supplier's issues"],
            'backoffice.tools.suppliers.backorders.' => ['purchase', 'purchase.index', "Supplier's backorders"],
            'backoffice.tools.suppliersBackorders.' => ['purchase', 'purchase.index', "Supplier's backorders"],
            'purchase.tools.auto_orders.' => ['purchase', 'purchase.index', 'Auto orders'],
            'purchase.tools.suppliersBackorders.' => ['purchase', 'purchase.index', "Supplier's backorders"],
            'purchase.tools.suppliers.map.' => ['purchase', 'purchase.index', "Supplier's map"],
            'purchase.tools.price_map.' => ['purchase', 'purchase.index', 'Price map'],
            'purchase.tools.suppliers.issues.' => ['purchase', 'purchase.index', "Supplier's issues"],
            'frontoffice.tools.backorders.' => ['sales', 'sales.index', 'Backorders'],
            'frontoffice.tools.product_issues.' => ['sales', 'sales.index', 'Product issues'],
            'frontoffice.tools.returns.' => ['sales', 'sales.index', 'Returns'],
            'frontoffice.tools.warranties.' => ['sales', 'sales.index', 'Warranties'],
            'sales.tools.backorders.' => ['sales', 'sales.index', 'Backorders'],
            'sales.tools.product_issues.' => ['sales', 'sales.index', 'Product issues'],
            'sales.tools.returns.' => ['sales', 'sales.index', 'Returns'],
            'sales.tools.warranties.' => ['sales', 'sales.index', 'Warranties'],
            'sales.tools.payment_links.' => ['sales', 'sales.index', 'Payment links'],
            'documentsManager.clean.' => ['documents', 'documentsManager.index', 'Document manager'],
            'documentsManager.legacy.' => ['documents', 'documentsManager.index', 'Document manager'],
            'checklist.legacy.' => ['checklist', 'checklist.index', 'Manager'],
        ];
    }

    private function areaBreadcrumbs(string $areaName, ?string $areaRoute, string $currentName, ?string $currentUrl = null): array
    {
        return [
            [
                'name' => $areaName,
                'url' => $areaRoute ? route($areaRoute) : null,
                'no_translation' => ctype_upper(substr($areaName, 0, 1)) ? 1 : 0,
            ],
            ['name' => $currentName, 'url' => $currentUrl ?? url()->current(), 'no_translation' => 1],
        ];
    }

    private function quotesBreadcrumbs(): array
    {
        $from = (string) request()->query('from', '');
        $referrer = (string) request()->headers->get('referer', '');
        $routeName = (string) request()->route()->getName();

        if ($from === 'backoffice' || str_starts_with($routeName, 'backoffice.') || str_starts_with($routeName, 'purchase.') || str_contains($referrer, '/admin')) {
            return [
                ['name' => 'purchase', 'url' => route('purchase.index')],
                ['name' => 'Quotes', 'url' => url()->current(), 'no_translation' => 1],
            ];
        }

        return [
            ['name' => 'sales', 'url' => route('sales.index')],
            ['name' => 'Quotes', 'url' => url()->current(), 'no_translation' => 1],
        ];
    }

    private function routeTailLabel(string $routeName, string $prefix): string
    {
        return str_replace(['.', '_'], ' ', ucfirst(str_replace($prefix, '', $routeName)));
    }
}
