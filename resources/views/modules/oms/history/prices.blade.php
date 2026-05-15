@extends('layouts.app')

@section('content')
<div class="oms-premium" style="margin-top: 15px;">

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">

                <div class="col-md-2">
                    <a href="{{ route('erp.oms.dashboard', ['left_tab' => 'invoices']) }}"
                       class="btn btn-outline-primary w-100 oms-btn-icon">
                        <i class="fa-solid fa-table-columns"></i> Dashboard
                    </a>
                </div>

                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Reference</label>
                    <input type="text"
                           name="reference"
                           value="{{ request('reference') }}"
                           class="form-control"
                           placeholder="Product / attribute">
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">From</label>
                    <input type="date"
                           name="date_from"
                           value="{{ request('date_from') }}"
                           class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">To</label>
                    <input type="date"
                           name="date_to"
                           value="{{ request('date_to') }}"
                           class="form-control">
                </div>

                <div class="col-md-1">
                    <button class="btn btn-outline-primary w-100 oms-btn-icon">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                </div>

                <div class="col-md-1">
                    <a href="{{ route('erp.oms.history.prices') }}"
                       class="btn btn-outline-secondary w-100 oms-btn-icon">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>

            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Purchase Supplier</th>
                        <th>Purchase EUR</th>
                        <th>Sale Supplier</th>
                        <th>Sale EUR</th>
                        <th>Currency</th>
                        <th>User</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($rows as $row)
                    @php
                        $reference = $row->display_reference_snapshot
                            ?? $row->attribute_reference_snapshot
                            ?? $row->product_reference_snapshot
                            ?? '-';

                        $currencyIso = strtoupper($row->invoice_currency_iso ?? 'EUR');

                        $oldPurchaseSupplier = $row->old_purchase_supplier_currency ?? null;
                        $newPurchaseSupplier = $row->new_purchase_supplier_currency ?? null;

                        $oldPurchaseEur = $row->old_purchase_eur ?? $row->old_wholesale_price_eur ?? null;
                        $newPurchaseEur = $row->new_purchase_eur ?? $row->new_wholesale_price_eur ?? null;

                        $oldSaleSupplier = $row->old_sale_supplier_currency ?? null;
                        $newSaleSupplier = $row->new_sale_supplier_currency ?? null;

                        $oldSaleEur = $row->old_sale_eur ?? null;
                        $newSaleEur = $row->new_sale_eur ?? null;

                        $userName = $row->user_name_snapshot ?? '-';

                        // 🧠 Comparações
                        $pSupDiff = ($oldPurchaseSupplier !== null && $newPurchaseSupplier !== null)
                            ? $newPurchaseSupplier <=> $oldPurchaseSupplier : 0;

                        $pEurDiff = ($oldPurchaseEur !== null && $newPurchaseEur !== null)
                            ? $newPurchaseEur <=> $oldPurchaseEur : 0;

                        $sSupDiff = ($oldSaleSupplier !== null && $newSaleSupplier !== null)
                            ? $newSaleSupplier <=> $oldSaleSupplier : 0;

                        $sEurDiff = ($oldSaleEur !== null && $newSaleEur !== null)
                            ? $newSaleEur <=> $oldSaleEur : 0;

                        $getColor = fn($d) => $d === 1 ? 'text-danger' : ($d === -1 ? 'text-success' : 'text-primary');
                    @endphp

                    <tr>
                        <td>
                            <strong>{{ $reference }}</strong>
                            <div class="small text-muted">
                                P: {{ $row->product_id ?? '-' }}
                                @if(!empty($row->product_attribute_id))
                                    | A: {{ $row->product_attribute_id }}
                                @endif
                            </div>
                        </td>

                        {{-- Purchase Supplier --}}
                        <td>
                            {{ $oldPurchaseSupplier !== null ? number_format($oldPurchaseSupplier, 2, ',', '.') : '-' }}
                            <span class="mx-1 {{ $getColor($pSupDiff) }}">→</span>
                            <strong class="{{ $getColor($pSupDiff) }}">
                                {{ $newPurchaseSupplier !== null ? number_format($newPurchaseSupplier, 2, ',', '.') : '-' }}
                            </strong>
                        </td>

                        {{-- Purchase EUR --}}
                        <td>
                            {{ $oldPurchaseEur !== null ? number_format($oldPurchaseEur, 2, ',', '.') : '-' }}
                            <span class="mx-1 {{ $getColor($pEurDiff) }}">→</span>
                            <strong class="{{ $getColor($pEurDiff) }}">
                                {{ $newPurchaseEur !== null ? number_format($newPurchaseEur, 2, ',', '.') : '-' }}
                            </strong>
                        </td>

                        {{-- Sale Supplier --}}
                        <td>
                            {{ $oldSaleSupplier !== null ? number_format($oldSaleSupplier, 2, ',', '.') : '-' }}
                            <span class="mx-1 {{ $getColor($sSupDiff) }}">→</span>
                            <strong class="{{ $getColor($sSupDiff) }}">
                                {{ $newSaleSupplier !== null ? number_format($newSaleSupplier, 2, ',', '.') : '-' }}
                            </strong>
                        </td>

                        {{-- Sale EUR --}}
                        <td>
                            {{ $oldSaleEur !== null ? number_format($oldSaleEur, 2, ',', '.') : '-' }}
                            <span class="mx-1 {{ $getColor($sEurDiff) }}">→</span>
                            <strong class="{{ $getColor($sEurDiff) }}">
                                {{ $newSaleEur !== null ? number_format($newSaleEur, 2, ',', '.') : '-' }}
                            </strong>
                        </td>

                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $currencyIso }}
                            </span>
                        </td>

                        <td>{{ $userName }}</td>

                        <td>{{ $row->created_at ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No price history to display.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $rows->links() }}
    </div>

</div>
@endsection