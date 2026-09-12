<?php

namespace App\Services\oms;

use App\Models\modules\oms\BilledOrder;
use App\Models\modules\oms\OrderNote;

class DocumentCommentService
{
    public function saveOrderNoteNotes(OrderNote $orderNote, array $data): OrderNote
    {
        if (array_key_exists('internal_note', $data)) {
            $orderNote->internal_note = $data['internal_note'];
        }
        if (array_key_exists('logistic_note', $data)) {
            $orderNote->logistic_note = $data['logistic_note'];
        }
        $orderNote->save();

        return $orderNote->fresh();
    }

    public function saveBilledOrderNotes(BilledOrder $billedOrder, array $data): BilledOrder
    {
        if (array_key_exists('internal_note', $data)) {
            $billedOrder->internal_note = $data['internal_note'];
        }
        if (array_key_exists('logistic_note', $data)) {
            $billedOrder->logistic_note = $data['logistic_note'];
        }
        $billedOrder->save();

        return $billedOrder->fresh();
    }

    public function getDocumentNotes(string $documentType, int $documentId): array
    {
        $document = $this->resolveDocument($documentType, $documentId);

        return [
            'internal_note' => $document?->internal_note,
            'logistic_note' => $document?->logistic_note,
            'has_internal_note' => !empty($document?->internal_note),
            'has_logistic_note' => !empty($document?->logistic_note),
            'has_any_note' => !empty($document?->internal_note) || !empty($document?->logistic_note),
        ];
    }

    protected function resolveDocument(string $documentType, int $documentId): OrderNote|BilledOrder|null
    {
        return match ($documentType) {
            'order_note' => OrderNote::find($documentId),
            'billed_order' => BilledOrder::find($documentId),
            default => null,
        };
    }
}
