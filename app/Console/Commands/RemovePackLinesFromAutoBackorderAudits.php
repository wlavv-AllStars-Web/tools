<?php

namespace App\Console\Commands;

use App\Models\AutoBackorderAudit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemovePackLinesFromAutoBackorderAudits extends Command
{
    protected $signature = 'auto-backorder:remove-pack-lines {--date= : Audit date in YYYY-MM-DD format}';

    protected $description = 'Removes pack header lines from existing auto-backorder audits.';

    public function handle(): int
    {
        $query = AutoBackorderAudit::query()->orderBy('id');

        if ($date = $this->option('date')) {
            $query->whereDate('audit_date', $date);
        }

        $audits = $query->get();
        $detailIds = $audits
            ->flatMap(fn (AutoBackorderAudit $audit) => collect($audit->unpicked_products)->pluck('id_order_detail'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($detailIds->isEmpty()) {
            $this->info('Não existem linhas de produto para tratar.');

            return self::SUCCESS;
        }

        $packDetailIds = DB::connection('mysql2')
            ->table('ps_order_detail as od')
            ->whereIn('od.id_order_detail', $detailIds)
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('ps_pack as pack')
                    ->whereColumn('pack.id_product_pack', 'od.product_id');
            })
            ->pluck('od.id_order_detail')
            ->flip();

        $updated = 0;
        $deleted = 0;

        foreach ($audits as $audit) {
            $products = collect($audit->unpicked_products);
            $remainingProducts = $products
                ->reject(fn (array $product) => $packDetailIds->has((int) ($product['id_order_detail'] ?? 0)))
                ->values()
                ->all();

            if (count($remainingProducts) === $products->count()) {
                continue;
            }

            if (empty($remainingProducts)) {
                $audit->delete();
                $deleted++;

                continue;
            }

            $audit->update(['unpicked_products' => $remainingProducts]);
            $updated++;
        }

        $this->info(sprintf('%d relatório(s) atualizado(s); %d relatório(s) sem linhas restantes removido(s).', $updated, $deleted));

        return self::SUCCESS;
    }
}
