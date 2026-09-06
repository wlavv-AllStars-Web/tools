<?php

namespace App\Console\Commands;

use App\Models\AutoBackorderAudit;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditShippedOrdersForBackorder extends Command
{
    protected $signature = 'auto-backorder:audit {--date= : Audit date in YYYY-MM-DD format} {--executed-at= : Recorded execution timestamp in YYYY-MM-DD HH:MM:SS format}';

    protected $description = 'Audits shipped orders with unpicked non-technical products (control = 0).';

    public function handle(): int
    {
        $auditDate = $this->option('date')
            ? Carbon::createFromFormat('Y-m-d', (string) $this->option('date'))->startOfDay()
            : now()->startOfDay();

        $executedAt = $this->option('executed-at')
            ? Carbon::createFromFormat('Y-m-d H:i:s', (string) $this->option('executed-at'))
            : now();

        $rows = DB::connection('mysql2')
            ->table('ps_orders as o')
            ->join('ps_order_detail as od', 'od.id_order', '=', 'o.id_order')
            ->leftJoin('ps_custom_order_detail as cod', 'cod.id_order_detail', '=', 'od.id_order_detail')
            ->select([
                'o.id_order',
                'o.reference as order_reference',
                'o.current_state',
                'od.id_order_detail',
                'od.product_reference',
                'od.product_name',
                'od.product_quantity',
                DB::raw('COALESCE(cod.control, 0) as control'),
            ])
            ->where('o.current_state', config('auto_backorder.shipped_state'))
            ->where('od.product_quantity', '>', 0)
            ->where(function ($query) {
                $query->whereNull('cod.control')->orWhere('cod.control', 0);
            })
            ->orderBy('o.id_order')
            ->orderBy('od.id_order_detail')
            ->get()
            ->reject(fn (object $row) => $this->isTechnicalReference((string) $row->product_reference));

        $orders = $rows->groupBy('id_order');
        $created = 0;

        foreach ($orders as $orderRows) {
            $first = $orderRows->first();
            $products = $orderRows->map(fn (object $row) => [
                'id_order_detail' => (int) $row->id_order_detail,
                'reference' => $row->product_reference,
                'name' => $row->product_name,
                'quantity' => (int) $row->product_quantity,
                'control' => (int) $row->control,
            ])->values()->all();

            $audit = AutoBackorderAudit::firstOrCreate(
                ['id_order' => (int) $first->id_order, 'audit_date' => $auditDate->toDateString()],
                [
                    'order_reference' => $first->order_reference,
                    'original_state' => (int) $first->current_state,
                    'target_state' => (int) config('auto_backorder.backorder_state'),
                    'detected_at' => $executedAt,
                    'reason' => 'Encomenda em shipped com produto(s) não picado(s) (control = 0).',
                    'unpicked_products' => $products,
                    'state_changed' => false,
                ],
            );

            $created += $audit->wasRecentlyCreated ? 1 : 0;
        }

        $this->info(sprintf('%d encomenda(s) elegível(eis); %d novo(s) registo(s) de auditoria.', $orders->count(), $created));

        return self::SUCCESS;
    }

    private function isTechnicalReference(string $reference): bool
    {
        $reference = mb_strtolower(trim($reference));

        if (in_array($reference, config('auto_backorder.ignored_references', []), true)) {
            return true;
        }

        foreach (config('auto_backorder.ignored_reference_prefixes', []) as $prefix) {
            if (str_starts_with($reference, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
