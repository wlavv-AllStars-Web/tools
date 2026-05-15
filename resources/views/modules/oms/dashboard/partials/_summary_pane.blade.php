@php
    $documentType = $summaryPane['document_type'] ?? null;
    $document = $summaryPane['document'] ?? null;
    $metrics = $summaryPane['metrics'] ?? [];
    $supplierMap = $summaryPane['supplier_map'] ?? null;
@endphp

@if(!$document)
    <div class="oms-empty">
        <i class="fa-solid fa-circle-info fa-2x mb-2"></i>
        <div>No document selected.</div>
    </div>

@elseif($documentType === 'order_note')
    @php
        $ordered = (int) ($metrics['qty_ordered'] ?? $metrics['ordered_units'] ?? 0);
        $billed = (int) ($metrics['qty_billed'] ?? $metrics['billed_units'] ?? 0);
        $received = (int) ($metrics['qty_received'] ?? $metrics['received_units'] ?? 0);
        $progress = $billed > 0 ? round(($received / max($billed, 1)) * 100) : 0;
        $progress = max(0, min(100, $progress));
        $internalFilled = !empty($document->internal_note);
        $logisticFilled = !empty($document->logistic_note);
        $supplierName = optional($document->supplier)->name ?? ('Supplier #' . $document->supplier_id);
        $currency = $supplierMap['currency'] ?? $supplierMap['currency_iso'] ?? $supplierMap['currency_code'] ?? '-';
    @endphp

    <div class="oms-card-header" style="padding: 0;">
        <div>
            <div class="small text-muted mb-1">ORDER NOTE</div>
            <div class="fw-bold mb-3" style="margin-bottom: 10px !important;">{{ $document->reference }}</div>
        </div>
    </div>

    <div class="small text-muted">Supplier</div>
    <div class="mb-2">{{ $supplierName }}</div>

    <div class="small text-muted">Currency</div>
    <div class="mb-2">{{ $currency }}</div>

    <div class="small text-muted">Created</div>
    <div class="mb-2">{{ optional($document->created_at)->format('Y-m-d H:i') }}</div>

    <div class="small text-muted">ETA</div>
    <div class="mb-2">{{ $summaryPane['eta'] ?? '-' }}</div>

    <div>
        <table style="width: 100%; text-align: center;">
            <tr>
                <td><div class="small text-muted">Ordered</div></td>
                <td><div class="small text-muted">Invoiced</div></td>
                <td><div class="small text-muted">Received</div></td>
            </tr>
            <tr>
                <td><div class="mb-2">{{ $ordered }}</div></td>
                <td><div class="mb-2">{{ $billed }}</div></td>
                <td><div class="mb-3">{{ $received }}</div></td>
            </tr>
        </table>
    </div>

    <div class="small text-muted mb-1">Reception vs Invoiced</div>
    <div class="d-flex justify-content-between align-items-center mb-1">
        <div class="small">{{ $progress }}%</div>
    </div>
    <div class="progress mb-3">
        <div class="progress-bar {{ $progress < 50 ? 'bg-danger' : ($progress < 100 ? 'bg-warning' : 'bg-success') }}" style="width: {{ $progress }}%;"></div>
    </div>

    <div class="d-flex gap-2 mb-3">
        <button class="btn btn-sm {{ $internalFilled ? 'btn-outline-danger' : 'btn-outline-secondary' }}" data-bs-toggle="modal" data-bs-target="#omsInternalNoteModal">
            <i class="fa-solid fa-note-sticky"></i>
        </button>
        <button class="btn btn-sm {{ $logisticFilled ? 'btn-outline-danger' : 'btn-outline-secondary' }}" data-bs-toggle="modal" data-bs-target="#omsLogisticNoteModal">
            <i class="fa-solid fa-truck-fast"></i>
        </button>
    </div>
    
    @if(!empty($supplierMap['exists']) || !empty($supplierMap))
        <div>
            <button class="btn btn-sm btn-outline-dark w-100 d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#omsSupplierMapCollapse" aria-expanded="false" style="border-radius: 2px !important;">
                <span>Supplier Info</span>
                <i class="fa-solid fa-chevron-down"></i>
            </button>
    
            <div class="collapse mt-2" id="omsSupplierMapCollapse">
                <div class="border rounded p-2" style="border-radius:5px;">
                    <div class="small text-muted">Contact</div>
                    <div class="mb-1">{{ $supplierMap['contact'] ?? '-' }}</div>
    
                    <div class="small text-muted">Email</div>
                    <div class="mb-1">{{ $supplierMap['email'] ?? '-' }}</div>
    
                    <div class="small text-muted">Phone</div>
                    <div class="mb-1">{{ $supplierMap['phone'] ?? '-' }}</div>
    
                    <div class="small text-muted">Incoterm</div>
                    <div class="mb-1">{{ $supplierMap['incoterm'] ?? '-' }}</div>
    
                    <div class="small text-muted">Country</div>
                    <div>{{ $supplierMap['country'] ?? '-' }}</div>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade" id="omsInternalNoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Internal Comment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="post" action="{{ route('erp.oms.order_notes.notes.save', $document) }}" class="oms-note-form">
                @csrf
                <div class="modal-body">
                    <textarea name="internal_note" class="form-control" rows="7">{{ $document->internal_note }}</textarea>
                    <input type="hidden" name="logistic_note" value="{{ $document->logistic_note }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save</button>
                </div>
            </form>
        </div></div>
    </div>

    <div class="modal fade" id="omsLogisticNoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Logistic Comment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="post" action="{{ route('erp.oms.order_notes.notes.save', $document) }}" class="oms-note-form">
                @csrf
                <div class="modal-body">
                    <textarea name="logistic_note" class="form-control" rows="7">{{ $document->logistic_note }}</textarea>
                    <input type="hidden" name="internal_note" value="{{ $document->internal_note }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save</button>
                </div>
            </form>
        </div></div>
    </div>

@elseif($documentType === 'billed_order')
    @php
        $billed = (int) ($metrics['qty_billed'] ?? $metrics['billed_units'] ?? 0);
        $received = (int) ($metrics['qty_received'] ?? $metrics['received_units'] ?? 0);
        $missing = (int) ($metrics['qty_missing_to_receive'] ?? $metrics['missing_units'] ?? 0);
        $progress = $billed > 0 ? round(($received / max($billed, 1)) * 100) : 0;
        $progress = max(0, min(100, $progress));
        $internalFilled = !empty($document->internal_note);
        $logisticFilled = !empty($document->logistic_note);
        $supplierName = optional(optional($document->orderNote)->supplier)->name ?? ('Supplier #' . optional($document->orderNote)->supplier_id);
        $currency = $supplierMap['currency'] ?? $supplierMap['currency_iso'] ?? $supplierMap['currency_code'] ?? '-';
    @endphp

    <div class="small text-muted mb-1">INVOICED</div>
    <div class="fw-bold mb-3">{{ $document->reference }}</div>

    <div class="small text-muted">Supplier</div>
    <div class="mb-2">{{ $supplierName }}</div>

    <div class="small text-muted">Order Note</div>
    <div class="mb-2">{{ optional($document->orderNote)->reference ?? '-' }}</div>

    <div class="small text-muted">Currency</div>
    <div class="mb-2">{{ $currency }}</div>

    <div class="small text-muted">Invoice</div>
    <div class="mb-2">{{ optional($document->invoice)->invoice_reference ?? '-' }}</div>

    <div>
        <table style="width: 100%; text-align: center;">
            <tr>
                <td><div class="small text-muted">Invoiced</div></td>
                <td><div class="small text-muted">Received</div></td>
                <td><div class="small text-muted">Missing</div></td>
            </tr>
            <tr>
                <td><div class="mb-2">{{ $billed }}</div></td>
                <td><div class="mb-3">{{ $received }}</div></td>
                <td><div class="mb-2">{{ $missing }}</div></td>
            </tr>
        </table>
    </div>

    <div class="small text-muted mb-1">Reception vs Invoiced</div>
    <div class="small mb-1">{{ $progress }}%</div>
    <div class="progress mb-3">
        <div class="progress-bar {{ $progress < 50 ? 'bg-danger' : ($progress < 100 ? 'bg-warning' : 'bg-success') }}" style="width: {{ $progress }}%;"></div>
    </div>

    <div class="d-flex gap-2 mb-3">
        <button class="btn btn-sm {{ $internalFilled ? 'btn-outline-danger' : 'btn-outline-secondary' }}" data-bs-toggle="modal" data-bs-target="#omsBiInternalNoteModal">
            <i class="fa-solid fa-note-sticky"></i>
        </button>
        <button class="btn btn-sm {{ $logisticFilled ? 'btn-outline-danger' : 'btn-outline-secondary' }}" data-bs-toggle="modal" data-bs-target="#omsBiLogisticNoteModal">
            <i class="fa-solid fa-truck-fast"></i>
        </button>
    </div>

    <div class="modal fade" id="omsBiInternalNoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Internal Comment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="post" action="{{ route('erp.oms.billed_orders.notes.save', $document) }}" class="oms-note-form">
                @csrf
                <div class="modal-body">
                    <textarea name="internal_note" class="form-control" rows="7">{{ $document->internal_note }}</textarea>
                    <input type="hidden" name="logistic_note" value="{{ $document->logistic_note }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save</button>
                </div>
            </form>
        </div></div>
    </div>

    <div class="modal fade" id="omsBiLogisticNoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Logistic Comment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="post" action="{{ route('erp.oms.billed_orders.notes.save', $document) }}" class="oms-note-form">
                @csrf
                <div class="modal-body">
                    <textarea name="logistic_note" class="form-control" rows="7">{{ $document->logistic_note }}</textarea>
                    <input type="hidden" name="internal_note" value="{{ $document->internal_note }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save</button>
                </div>
            </form>
        </div></div>
    </div>

@elseif($documentType === 'invoice')
    @php
        $billed = (int) ($metrics['qty_billed'] ?? $metrics['billed_units'] ?? 0);
        $received = (int) ($metrics['qty_received'] ?? $metrics['received_units'] ?? 0);
        $missing = (int) ($metrics['qty_missing_to_receive'] ?? $metrics['missing_units'] ?? 0);
        $progress = $billed > 0 ? round(($received / max($billed, 1)) * 100) : 0;
        $progress = max(0, min(100, $progress));
        $supplierName = optional($document->supplier)->name ?? ('Supplier #' . $document->supplier_id);
        $currency = $supplierMap['currency'] ?? $supplierMap['currency_iso'] ?? $supplierMap['currency_code'] ?? '-';
        $orderNoteRefs = collect($document->billedOrders ?? [])->map(fn($bo) => optional($bo->orderNote)->reference)->filter()->unique()->implode(', ');
    @endphp

    <div class="small text-muted mb-1">INVOICE</div>
    <div class="fw-bold mb-3">{{ $document->invoice_reference }}</div>

    <div class="small text-muted">Supplier</div>
    <div class="mb-2">{{ $supplierName }}</div>

    <div class="small text-muted">Order Notes</div>
    <div class="mb-2">{{ $orderNoteRefs ?: '-' }}</div>

    <div class="small text-muted">Currency</div>
    <div class="mb-2">{{ $currency }}</div>

    <div class="small text-muted">Invoice Date</div>
    <div class="mb-2">{{ optional($document->invoice_date)->format('Y-m-d') }}</div>

    <div>
        <table style="width: 100%; text-align: center;">
            <tr>
                <td><div class="small text-muted">Invoiced</div></td>
                <td><div class="small text-muted">Received</div></td>
                <td><div class="small text-muted">Missing</div></td>
            </tr>
            <tr>
                <td><div class="mb-2">{{ $billed }}</div></td>
                <td><div class="mb-3">{{ $received }}</div></td>
                <td><div class="mb-2">{{ $missing }}</div></td>
            </tr>
        </table>
    </div>

    <div class="small text-muted mb-1">Reception vs Invoiced</div>
    <div class="small mb-1">{{ $progress }}%</div>
    <div class="progress mb-3">
        <div class="progress-bar {{ $progress < 50 ? 'bg-danger' : ($progress < 100 ? 'bg-warning' : 'bg-success') }}" style="width: {{ $progress }}%;"></div>
    </div>
@endif
