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
                           placeholder="Product / attribute reference">
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
                    <a href="{{ route('erp.oms.history.stock') }}"
                       class="btn btn-outline-secondary w-100 oms-btn-icon">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>

            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Stock Before</th>
                        <th>Stock Delta</th>
                        <th>Stock After</th>
                        <th>Arrive Before</th>
                        <th>Arrive Delta</th>
                        <th>Arrive After</th>
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

                        $stockBefore = $row->ps_quantity_before ?? $row->stock_before ?? null;
                        $stockDelta = $row->ps_quantity_delta ?? $row->quantity_received ?? null;
                        $stockAfter = $row->ps_quantity_after ?? $row->stock_after ?? null;

                        $arriveBefore = $row->ps_quantity_arrive_before ?? null;
                        $arriveDelta = $row->ps_quantity_arrive_delta ?? null;
                        $arriveAfter = $row->ps_quantity_arrive_after ?? null;

                        $userName = $row->user_name_snapshot
                            ?? $row->changed_by_user_name
                            ?? $row->user_name
                            ?? '-';    
                            
                            
                            $getDeltaClass = function ($value) {
                            $v = (float) ($value ?? 0);
                    
                            if ($v > 0) {
                                return 'bg-success';   // verde
                            }
                    
                            if ($v < 0) {
                                return 'bg-danger';    // vermelho
                            }
                    
                            return 'bg-primary';       // dodgerblue (bootstrap primary)
                        };
                    
                        $stockDeltaClass = $getDeltaClass($stockDelta);
                        $arriveDeltaClass = $getDeltaClass($arriveDelta);
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

                        <td>{{ $stockBefore !== null ? number_format((float) $stockBefore, 0, ',', '.') : '-' }}</td>

                        <td>
                            <span class="badge {{ $stockDeltaClass }}">
                                {{ $stockDelta !== null && (float) $stockDelta > 0 ? '+' : '' }}{{ $stockDelta !== null ? number_format((float) $stockDelta, 0, ',', '.') : '-' }}
                            </span>
                        </td>

                        <td>
                            <strong>{{ $stockAfter !== null ? number_format((float) $stockAfter, 0, ',', '.') : '-' }}</strong>
                        </td>

                        <td>{{ $arriveBefore !== null ? number_format((float) $arriveBefore, 0, ',', '.') : '-' }}</td>

                        <td>
                            <span class="badge {{ $arriveDeltaClass }}">
                                {{ $arriveDelta !== null && (float) $arriveDelta > 0 ? '+' : '' }}{{ $arriveDelta !== null ? number_format((float) $arriveDelta, 0, ',', '.') : '-' }}
                            </span>
                        </td>

                        <td>
                            <strong>{{ $arriveAfter !== null ? number_format((float) $arriveAfter, 0, ',', '.') : '-' }}</strong>
                        </td>

                        <td>{{ $userName }}</td>

                        <td>{{ $row->created_at ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            No stock history to display.
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