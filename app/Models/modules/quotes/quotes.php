<?php

namespace App\Models\modules\quotes;

use Illuminate\Database\Eloquent\Model;

use App\Models\prestashop\asm_dashboard;

use App\Models\Concerns\BuildsDashboardPanels;
class quotes extends Model
{
    
    use BuildsDashboardPanels;
protected $table = 'quotes';

    protected $fillable = [
        'referencia',
        'brand',
        'notas_front',
        'price',
        'lead',
        'notas_back',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public static function dashboard_quote_backoffice($type)
    {
        $exceptions = asm_dashboard::getExceptions('quotes_back')
            ->pluck('id_product')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    
        $rows = self::select('id', 'brand', 'referencia', 'notas_front', 'status')
            ->where('status', 'new')
            ->when(!empty($exceptions), fn ($query) => $query->whereNotIn('id', $exceptions))
            ->orderBy('id', 'DESC')
            ->get();
            
        return self::dashboardPanel(
            'QUOTE',
            $type,
            'product_requests_front',
            ['clean', 'id', 'brand', 'reference'],
            $rows->map(fn ($item) => [
                'clean' => $item->id,
                'id' => $item->id,
                'brand' => $item->brand,
                'reference' => $item->referencia,
                'status' => strtoupper(str_replace('_', ' ', $item->status)),
            ]),
            [
                'exception_fields' => ['quotes_back', 'id', 'brand', 'reference'],
                'link' => route('quotes.index', [1]),
            ],
            null
        );
    }

    public static function dashboard_quote_frontoffice($type)
    {
        $exceptions = asm_dashboard::getExceptions('quotes_front')
            ->pluck('id_product')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        $rows = self::select('id', 'brand', 'referencia', 'notas_back', 'status')
            ->where('status', '<>', 'new')
            ->when(!empty($exceptions), fn ($query) => $query->whereNotIn('id', $exceptions))
            ->orderBy('id', 'DESC')
            ->get();

        return self::dashboardPanel(
            "PRODUCT REQUEST'S ( Frontoffice )",
            $type,
            'product_requests_back',
            ['clean', 'id', 'brand', 'reference'],
            $rows->map(fn ($item) => [
                'clean' => $item->id,
                'id' => $item->id,
                'brand' => $item->brand,
                'reference' => $item->referencia,
                'status' => strtoupper(str_replace('_', ' ', $item->status)),
            ]),
            [
                'exception_fields' => ['quotes_front', 'id', 'brand', 'reference'],
                'link' => route('sales.tools.quotes.index', ['list' => 1]),
            ],
            null
        );
    }
    
}
