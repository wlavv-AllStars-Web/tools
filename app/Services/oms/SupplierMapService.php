<?php

namespace App\Services\oms;

use Illuminate\Support\Facades\DB;

class SupplierMapService
{
    protected string $table = 'supplier_map';

    public function getBySupplierId(int $supplierId): ?object
    {
        return DB::table($this->table)
            ->where('id_supplier', $supplierId)
            ->first();
    }

    public function getSummaryBySupplierId(int $supplierId): array
    {
        $row = $this->getBySupplierId($supplierId);

        if (!$row) {
            return [
                'exists' => false,
                'contact' => null,
                'email' => null,
                'phone' => null,
                'website' => null,
                'dealer_website' => null,
                'warranty' => null,
                'currency' => null,
                'incoterm' => null,
                'description' => null,
            ];
        }

        return [
            'exists' => true,
            'contact' => $row->contact ?? null,
            'email' => $row->email ?? null,
            'phone' => $row->phone ?? null,
            'website' => $row->website ?? null,
            'dealer_website' => $row->dealer_website ?? null,
            'warranty' => $row->warranty ?? null,
            'currency' => $row->currency ?? null,
            'incoterm' => $row->incoterm ?? null,
            'description' => $row->description ?? null,
            'address' => $row->address ?? null,
            'country' => $row->country ?? null,
            'terms' => $row->terms ?? null,
            'discount' => $row->discount ?? null,
        ];
    }
}
