@extends('layouts.app')

@section('content')
<div class="container-fluid py-3 oms-on-import-verify">
    <style>
        .oms-on-import-verify .oms-card,
        .oms-on-import-verify .btn,
        .oms-on-import-verify .badge,
        .oms-on-import-verify .form-control,
        .oms-on-import-verify .table,
        .oms-on-import-verify .modal-content,
        .oms-on-import-verify .list-group-item { border-radius: 5px !important; }
        .oms-on-import-verify .oms-card {
            border: 1px solid rgba(20, 33, 61, .08);
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .05);
            overflow: hidden;
        }
        .oms-on-import-verify .oms-card-header {
            padding: .85rem 1rem;
            border-bottom: 1px solid rgba(20, 33, 61, .07);
            background: #f8fafc;
        }
        .oms-on-import-verify .oms-card-body { padding: 1rem; }
        .oms-on-import-verify .oms-topbar {
            display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; margin-bottom:1rem;
        }
        .oms-on-import-verify .oms-topbar-block { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
        .oms-on-import-verify .oms-count-chip,
        .oms-on-import-verify .oms-selected-chip {
            display:inline-flex; align-items:center; gap:.45rem; padding:.5rem .7rem; border:1px solid rgba(20,33,61,.10); background:#fff; border-radius:5px; font-weight:600; color:#1f2937; min-height:38px;
        }
        .oms-on-import-verify .oms-selected-chip { min-width:320px; justify-content:center; }
        .oms-on-import-verify .oms-kpi-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.75rem; }
        .oms-on-import-verify .oms-kpi {
            border:1px solid rgba(20,33,61,.08); background:#fff; border-radius:5px; padding:.8rem .9rem;
        }
        .oms-on-import-verify .oms-kpi .label { display:block; font-size:.72rem; text-transform:uppercase; color:#6b7280; letter-spacing:.05em; font-weight:700; margin-bottom:.2rem; }
        .oms-on-import-verify .oms-kpi .value { display:block; font-size:1.15rem; font-weight:800; color:#111827; }
        .oms-on-import-verify .table > :not(caption) > * > * { vertical-align:middle; }
        .oms-on-import-verify .oms-code { font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace; background:#f8fafc; border:1px solid rgba(20,33,61,.08); padding:.18rem .42rem; border-radius:5px; }
        .oms-on-import-verify .oms-status-badge { min-width:88px; display:inline-flex; justify-content:center; }
        @media (max-width: 991.98px) {
            .oms-on-import-verify .oms-kpi-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
            .oms-on-import-verify .oms-selected-chip { min-width:auto; justify-content:flex-start; }
        }
    </style>

    @php
        $summary = $preview['summary'] ?? [];
        $rows = collect($preview['rows'] ?? []);
        $supplierName = trim((string) optional($orderNote->supplier)->name) ?: ('Supplier #' . $orderNote->supplier_id);
    @endphp

    <div class="oms-topbar">
        <div class="oms-topbar-block">
            <div class="oms-count-chip"><span>Total rows</span> <span class="badge text-bg-secondary">{{ (int) ($summary['total_rows'] ?? 0) }}</span></div>
            <div class="oms-count-chip"><span>Valid</span> <span class="badge text-bg-success">{{ (int) ($summary['valid_rows'] ?? 0) }}</span></div>
            <div class="oms-count-chip"><span>Invalid</span> <span class="badge text-bg-danger">{{ (int) ($summary['invalid_rows'] ?? 0) }}</span></div>
            <div class="oms-count-chip"><span>Duplicates</span> <span class="badge text-bg-warning">{{ (int) ($summary['duplicate_rows'] ?? 0) }}</span></div>
        </div>

        <div class="oms-topbar-block flex-grow-1 justify-content-center">
            <div class="oms-selected-chip">
                <i class="fa-solid fa-file-arrow-up text-success"></i>
                <span>{{ $orderNote->reference }}</span>
                <span class="text-muted">·</span>
                <span>{{ $supplierName }}</span>
            </div>
        </div>

        <div class="oms-topbar-block justify-content-end">
            <a href="{{ route('erp.oms.order_notes.show', $orderNote) }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-angle-left me-1"></i> Back
            </a>
            <form method="post" action="{{ route('erp.oms.order_notes.import.confirm', $orderNote) }}">
                @csrf
                <button type="submit" class="btn btn-outline-success" {{ !$canMutateLines || $rows->where('is_valid', true)->isEmpty() ? 'disabled' : '' }}>
                    <i class="fa-solid fa-file-import me-1"></i> Confirm import
                </button>
            </form>
        </div>
    </div>

    <div class="oms-card mb-3">
        <div class="oms-card-body">
            <div class="oms-kpi-grid">
                <div class="oms-kpi">
                    <span class="label">File</span>
                    <span class="value" style="font-size:.95rem;">{{ $preview['filename'] ?? '—' }}</span>
                </div>
                <div class="oms-kpi">
                    <span class="label">Uploaded at</span>
                    <span class="value" style="font-size:.95rem;">{{ $preview['uploaded_at'] ?? '—' }}</span>
                </div>
                <div class="oms-kpi">
                    <span class="label">Delimiter</span>
                    <span class="value">{{ ($preview['delimiter'] ?? ',') === "	" ? 'TAB' : ($preview['delimiter'] ?? ',') }}</span>
                </div>
                <div class="oms-kpi">
                    <span class="label">Total qty</span>
                    <span class="value">{{ (int) ($summary['total_qty'] ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="oms-card">
        <div class="oms-card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <div>
                <div class="small text-muted text-uppercase fw-semibold">Verification</div>
                <div class="fw-bold">Review import lines before confirmation</div>
            </div>
            <div class="small text-muted">Only valid rows will be imported. The file must use reference + quantity. Duplicate references in the same CSV are merged on confirmation.</div>
        </div>
        <div class="oms-card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Row</th>
                            <th>Input</th>
                            <th>Resolved product</th>
                            <th class="text-center">Qty</th>
                            <th class="text-center">Status</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td class="text-center">{{ $row['row_number'] }}</td>
                                <td>
                                    <div class="d-flex flex-column gap-1">                                        <div><span class="oms-code">{{ $row['input_reference'] ?: '—' }}</span></div>
                                        <div class="small text-muted">Reference from CSV</div>
                                    </div>
                                </td>
                                <td>
                                    @if($row['is_valid'])
                                        <div class="fw-semibold">{{ $row['resolved_name'] }}</div>
                                        <div class="small text-muted">
                                            <span class="oms-code">{{ $row['resolved_reference'] ?: '—' }}</span>
                                            · P#{{ $row['product_id'] }} | A#{{ $row['product_attribute_id'] ?: 0 }}
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center fw-semibold">{{ (int) ($row['qty_ordered'] ?? 0) }}</td>
                                <td class="text-center">
                                    <span class="badge text-bg-{{ $row['status_class'] }} oms-status-badge">{{ $row['status_label'] }}</span>
                                </td>
                                <td class="small">{{ $row['message'] ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No preview rows available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
