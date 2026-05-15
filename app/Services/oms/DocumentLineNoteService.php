<?php

namespace App\Services\oms;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DocumentLineNoteService
{
    protected string $table = 'oms_document_line_notes';

    public function save(string $documentType, int $documentLineId, array $notes): void
    {
        $allowed = ['warranty', 'components', 'replacement'];

        foreach ($allowed as $type) {
            if (!array_key_exists($type, $notes)) {
                continue;
            }

            $content = is_null($notes[$type]) ? null : trim((string) $notes[$type]);

            $existing = DB::table($this->table)
                ->where('document_type', $documentType)
                ->where('document_line_id', $documentLineId)
                ->where('note_type', $type)
                ->first();

            if ($content === '' || $content === null) {
                if ($existing) {
                    DB::table($this->table)
                        ->where('id', $existing->id)
                        ->delete();
                }
                continue;
            }

            if ($existing) {
                DB::table($this->table)
                    ->where('id', $existing->id)
                    ->update([
                        'content' => $content,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table($this->table)->insert([
                    'document_type' => $documentType,
                    'document_line_id' => $documentLineId,
                    'note_type' => $type,
                    'content' => $content,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function getByLine(string $documentType, int $documentLineId): array
    {
        $rows = DB::table($this->table)
            ->where('document_type', $documentType)
            ->where('document_line_id', $documentLineId)
            ->get();

        $data = [
            'warranty' => null,
            'components' => null,
            'replacement' => null,
            'has_any' => false,
        ];

        foreach ($rows as $row) {
            if (in_array($row->note_type, ['warranty', 'components', 'replacement'], true)) {
                $data[$row->note_type] = $row->content;
            }
        }

        $data['has_any'] = !empty($data['warranty']) || !empty($data['components']) || !empty($data['replacement']);

        return $data;
    }
}
