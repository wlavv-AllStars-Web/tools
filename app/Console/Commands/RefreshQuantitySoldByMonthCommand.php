<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefreshQuantitySoldByMonthCommand extends Command
{
    protected $signature = 'sales:populate-quantity-sold-by-month';

    protected $description = 'Populate a fixed snapshot of the last 12 months of effective PS16 sales';

    public function handle(): int
    {
        if (!Schema::connection('mysql')->hasTable('quantity_sold_by_month')) {
            $this->error('Table quantity_sold_by_month does not exist. Run its migration first.');

            return self::FAILURE;
        }

        if (DB::connection('mysql')->table('quantity_sold_by_month')->exists()) {
            $this->error('Table quantity_sold_by_month already contains its fixed snapshot.');

            return self::FAILURE;
        }

        $start = now()->startOfMonth()->subMonths(11);
        $end = now();
        $rows = $this->sourceRows($start, $end);

        DB::connection('mysql')->transaction(function () use ($rows) {
            $rows->chunk(1000)->each(function ($chunk) {
                DB::connection('mysql')
                    ->table('quantity_sold_by_month')
                    ->insert($chunk->all());
            });
        });

        $this->info(sprintf(
            'Created a fixed snapshot with %d monthly product/attribute rows from PS16 (%s to %s).',
            $rows->count(),
            $start->format('Y-m'),
            $end->format('Y-m')
        ));

        return self::SUCCESS;
    }

    private function sourceRows(Carbon $start, Carbon $end)
    {
        $prefix = env('DB2_DB_prefix', env('DB2_prefix', 'ps_'));
        $paidStates = array_map(
            'intval',
            config('allstars.auto_orders.paid_order_states', [2, 3, 4, 5, 15, 16, 28])
        );

        return DB::connection('mysql2')
            ->table($prefix . 'orders as o')
            ->join($prefix . 'order_detail as od', 'od.id_order', '=', 'o.id_order')
            ->leftJoin($prefix . 'product as p', 'p.id_product', '=', 'od.product_id')
            ->leftJoin($prefix . 'product_attribute as pa', 'pa.id_product_attribute', '=', 'od.product_attribute_id')
            ->where('o.valid', 1)
            ->whereIn('o.current_state', $paidStates)
            ->whereBetween('o.date_add', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->groupBy(
                'od.product_id',
                'od.product_attribute_id',
                'p.reference',
                'pa.reference',
                DB::raw('YEAR(o.date_add)'),
                DB::raw('MONTH(o.date_add)')
            )
            ->orderBy('od.product_id')
            ->orderBy('od.product_attribute_id')
            ->select([
                'od.product_id as id_product',
                'od.product_attribute_id as id_product_attribute',
                'p.reference',
                'pa.reference as attribute_reference',
                DB::raw('DATE_SUB(DATE(o.date_add), INTERVAL (DAYOFMONTH(o.date_add) - 1) DAY) as month'),
                DB::raw(
                    'SUM(GREATEST(od.product_quantity - GREATEST('
                    . 'COALESCE(od.product_quantity_refunded, 0), '
                    . 'COALESCE(od.product_quantity_return, 0)'
                    . '), 0)) as quantity_sold'
                ),
            ])
            ->get()
            ->map(fn ($row) => [
                'id_product' => (int) $row->id_product,
                'id_product_attribute' => (int) $row->id_product_attribute,
                'reference' => $row->reference,
                'attribute_reference' => $row->attribute_reference,
                'month' => $row->month,
                'quantity_sold' => (int) $row->quantity_sold,
                'calculated_at' => Carbon::now(),
            ]);
    }
}
