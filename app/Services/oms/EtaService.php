<?php

namespace App\Services\oms;

use App\Models\modules\oms\OrderNote;
use Illuminate\Support\Facades\DB;

class EtaService
{
    public function getLatestEtaForOrderNote(int $orderNoteId): ?string
    {
        return DB::table('shipping_erp as se')
            ->join('shipping_delay as sd', 'sd.id_shipping', '=', 'se.id_shipping')
            ->where('se.id_erp', $orderNoteId)
            ->max('sd.date');
    }

    public function getLatestEtaForOrderNoteModel(OrderNote $orderNote): ?string
    {
        return $this->getLatestEtaForOrderNote((int) $orderNote->id);
    }

    public function getEtaHistoryForOrderNote(int $orderNoteId)
    {
        return DB::table('shipping_erp as se')
            ->join('shipping_delay as sd', 'sd.id_shipping', '=', 'se.id_shipping')
            ->where('se.id_erp', $orderNoteId)
            ->orderByDesc('sd.date')
            ->get([
                'se.id_shipping',
                'sd.id',
                'sd.date',
            ]);
    }

    public function mapLatestEtasForOrderNotes(iterable $orderNoteIds): array
    {
        $ids = collect($orderNoteIds)
            ->filter(fn ($id) => !empty($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $rows = DB::table('shipping_erp as se')
            ->join('shipping_delay as sd', 'sd.id_shipping', '=', 'se.id_shipping')
            ->whereIn('se.id_erp', $ids)
            ->select('se.id_erp', DB::raw('MAX(sd.date) as eta'))
            ->groupBy('se.id_erp')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->id_erp] = $row->eta;
        }

        return $map;
    }
}
