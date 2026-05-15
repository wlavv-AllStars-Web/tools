<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PackingListExport implements FromArray, WithHeadings
{
    public function __construct(private array $items) {}

    public function headings(): array
    {
        return ['Referencia', 'HS Code', 'Peso', 'Comprimento', 'Largura', 'Altura', 'Quantidade faturada'];
    }

    public function array(): array
    {
        return array_map(function ($it) {
            return [
                $it['referencia'] ?? '',
                $it['hs_code'] ?? '',
                $it['weight'] ?? '',
                $it['comprimento'] ?? '',
                $it['largura'] ?? '',
                $it['altura'] ?? '',
                $it['quantidade'] ?? '',
            ];
        }, $this->items);
    }
}
