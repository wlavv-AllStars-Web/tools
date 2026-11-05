@extends('layouts.app')

@section('content')
<div class="navbar navbar-light customPanel" id="top">
    <div style="width:100%; display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:10px;">
        <h5 style="margin:0;">Carrier end of day</h5>
        <form method="GET" style="display:flex; gap:8px; margin:0;">
            <input type="date" name="date" class="form-control" value="{{ $date }}" style="width:170px;">
            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-filter"></i></button>
        </form>
    </div>

    @if(session('success')) <div class="alert alert-success" style="width:100%;">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger" style="width:100%;">{{ session('error') }}</div> @endif

    <div style="width:100%; display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:10px; margin-bottom:12px;">
        @forelse($byCarrier as $carrierName => $rows)
            @php
                $document = $documents->get($carrierName);
            @endphp
            <div style="border:1px solid #ddd; border-radius:5px; padding:10px;">
                <div style="font-weight:bolder; text-transform:uppercase;">{{ $carrierName }}</div>
                <div style="font-size:28px; font-weight:bolder; color:dodgerblue;">{{ $rows->count() }}</div>
                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    <a class="btn btn-secondary btn-sm" href="#carrier-{{ \Illuminate\Support\Str::slug($carrierName) }}">List</a>
                    <form method="POST" action="{{ route('logistics.tools.carrier_end_of_day.store') }}" style="margin:0;" onsubmit="return confirm(@js('Generate end of day document for ' . $carrierName . ' on ' . $date . '? Existing archive for this carrier/date will be replaced.'));">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date }}">
                        <input type="hidden" name="carrier_name" value="{{ $carrierName }}">
                        <button class="btn btn-primary btn-sm" type="submit">Generate</button>
                    </form>
                    @if($document)
                        <a class="btn btn-success btn-sm" href="{{ route('logistics.tools.carrier_end_of_day.pdf', $document) }}">PDF</a>
                        <a class="btn btn-warning btn-sm" href="{{ route('logistics.tools.carrier_end_of_day.print', $document) }}" target="_blank">Print</a>
                    @endif
                </div>
            </div>
        @empty
            <div class="alert alert-warning" style="grid-column:1 / -1; margin:0;">No shipments found for this date.</div>
        @endforelse
    </div>

    @if($byCarrier->isNotEmpty())
        <form method="POST" action="{{ route('logistics.tools.carrier_end_of_day.checks.store') }}" style="width:100%;">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <div style="width:100%; border-top:1px solid #ddd; padding-top:14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:10px;">
                    <h5 style="margin:0;">Carrier check</h5>
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save checks</button>
                </div>
                <div style="width:100%; overflow-x:auto;">
                    <table class="table table-bordered customTable text-center" style="min-width:850px; margin-bottom:0;">
                        <thead>
                            <tr style="text-transform:uppercase;">
                                <th>Carrier</th>
                                <th>Shipments</th>
                                <th>Checked</th>
                                <th>Non-standard</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byCarrier as $carrierName => $rows)
                                @php
                                    $check = $checks->get($carrierName);
                                    $inputKey = \Illuminate\Support\Str::slug($carrierName);
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $carrierName }}</strong>
                                        <input type="hidden" name="checks[{{ $inputKey }}][carrier_name]" value="{{ $carrierName }}">
                                    </td>
                                    <td>{{ $rows->count() }}</td>
                                    <td><input class="form-control text-center" type="number" min="0" name="checks[{{ $inputKey }}][qty_checked]" value="{{ optional($check)->qty_checked ?? $rows->count() }}"></td>
                                    <td><input class="form-control text-center" type="number" min="0" name="checks[{{ $inputKey }}][non_standard]" value="{{ optional($check)->non_standard ?? 0 }}"></td>
                                    <td><input class="form-control" type="text" maxlength="2000" name="checks[{{ $inputKey }}][note]" value="{{ optional($check)->note }}" placeholder="Note"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    @endif
</div>

@foreach($byCarrier as $carrierName => $rows)
    <div class="navbar navbar-light customPanel" id="carrier-{{ \Illuminate\Support\Str::slug($carrierName) }}">
        <div style="width:100%; display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <h5 style="margin:0;">{{ $carrierName }} - {{ $rows->count() }}</h5>
            <a href="#top" class="btn btn-secondary btn-sm">Top</a>
        </div>
        <div style="width:100%; border:1px solid #ddd; border-radius:5px; padding:10px; margin-bottom:10px;">
            <div style="display:grid; grid-template-columns: repeat(4, minmax(120px, 1fr)); gap:8px;">
                <input class="form-control carrier-eod-filter" data-carrier-filter="{{ \Illuminate\Support\Str::slug($carrierName) }}" data-filter="order" placeholder="Order ID / Reference">
                <input class="form-control carrier-eod-filter" data-carrier-filter="{{ \Illuminate\Support\Str::slug($carrierName) }}" data-filter="country" placeholder="Country">
                <input class="form-control carrier-eod-filter" data-carrier-filter="{{ \Illuminate\Support\Str::slug($carrierName) }}" data-filter="tracking" placeholder="Tracking">
                <button class="btn btn-secondary" type="button" onclick="clearCarrierEodFilters('{{ \Illuminate\Support\Str::slug($carrierName) }}')">Clear filters</button>
            </div>
        </div>
        <div style="width:100%; overflow-x:auto;">
            <table class="table table-bordered customTable text-center" style="min-width:1050px;">
                <thead>
                    <tr style="text-transform:uppercase;">
                        <th>Order ID</th>
                        <th>Reference</th>
                        <th>Country</th>
                        <th>Weight</th>
                        <th>Width</th>
                        <th>Length</th>
                        <th>Depth</th>
                        <th>Tracking</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr class="carrier-eod-row"
                            data-carrier-group="{{ \Illuminate\Support\Str::slug($carrierName) }}"
                            data-order="{{ strtolower($row->order_id . ' ' . $row->order_reference) }}"
                            data-country="{{ strtolower((string) $row->country) }}"
                            data-tracking="{{ strtolower((string) $row->tracking_number) }}">
                            <td>
                                @if($row->order_admin_url)
                                    <a href="{{ $row->order_admin_url }}" target="_blank">{{ $row->order_id }}</a>
                                @else
                                    {{ $row->order_id }}
                                @endif
                            </td>
                            <td>
                                @if($row->order_admin_url)
                                    <a href="{{ $row->order_admin_url }}" target="_blank">{{ $row->order_reference }}</a>
                                @else
                                    {{ $row->order_reference }}
                                @endif
                            </td>
                            <td>{{ $row->country }}</td>
                            <td>{{ $row->weight !== null ? number_format((float) $row->weight, 2, '.', '') : '-' }}</td>
                            <td>{{ $row->width ?? '-' }}</td>
                            <td>{{ $row->length ?? '-' }}</td>
                            <td>{{ $row->depth ?? '-' }}</td>
                            <td style="word-break:break-word;">{{ $row->tracking_number }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach

<script>
function applyCarrierEodFilters(carrierGroup) {
    const filters = {};
    document.querySelectorAll('.carrier-eod-filter[data-carrier-filter="' + carrierGroup + '"]').forEach(input => {
        filters[input.dataset.filter] = String(input.value || '').trim().toLowerCase();
    });

    document.querySelectorAll('.carrier-eod-row[data-carrier-group="' + carrierGroup + '"]').forEach(row => {
        const visible = Object.keys(filters).every(key => {
            return !filters[key] || String(row.dataset[key] || '').includes(filters[key]);
        });
        row.style.display = visible ? '' : 'none';
    });
}

function clearCarrierEodFilters(carrierGroup) {
    document.querySelectorAll('.carrier-eod-filter[data-carrier-filter="' + carrierGroup + '"]').forEach(input => input.value = '');
    applyCarrierEodFilters(carrierGroup);
}

document.querySelectorAll('.carrier-eod-filter').forEach(input => {
    input.addEventListener('input', function () {
        applyCarrierEodFilters(input.dataset.carrierFilter);
    });
});
</script>

<div class="navbar navbar-light customPanel">
    <h5 style="width:100%; margin:0 0 10px 0;">Recent documents</h5>
    <table class="table table-bordered customTable text-center" style="width:100%;">
        <thead>
            <tr style="text-transform:uppercase;">
                <th>Date</th>
                <th>Carrier</th>
                <th>Shipments</th>
                <th>Generated at</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($history as $document)
                <tr>
                    <td>{{ $document->document_date->format('Y-m-d') }}</td>
                    <td>{{ $document->carrier_name }}</td>
                    <td>{{ $document->shipments_count }}</td>
                    <td>{{ optional($document->generated_at)->format('Y-m-d H:i') }}</td>
                    <td>
                        <a class="btn btn-primary btn-sm" href="{{ route('logistics.tools.carrier_end_of_day.show', $document) }}">View</a>
                        <a class="btn btn-success btn-sm" href="{{ route('logistics.tools.carrier_end_of_day.pdf', $document) }}">PDF</a>
                        <a class="btn btn-warning btn-sm" href="{{ route('logistics.tools.carrier_end_of_day.print', $document) }}" target="_blank">Print</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No archived documents.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="navbar navbar-light customPanel" id="carrier-check-history">
    <div style="width:100%; display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:10px; flex-wrap:wrap;">
        <h5 style="margin:0;">Carrier check history</h5>
        <form method="GET" style="display:flex; gap:8px; margin:0; align-items:center;">
            <input type="hidden" name="date" value="{{ $date }}">
            <select name="check_year" class="form-control" style="width:95px;">
                <option value="">Year</option>
                @for($year = now()->year; $year >= 2023; $year--)
                    <option value="{{ $year }}" {{ (int) request('check_year') === $year ? 'selected' : '' }}>{{ $year }}</option>
                @endfor
            </select>
            <select name="check_month" class="form-control" style="width:95px;">
                <option value="">Month</option>
                @for($month = 1; $month <= 12; $month++)
                    <option value="{{ $month }}" {{ (int) request('check_month') === $month ? 'selected' : '' }}>{{ $month }}</option>
                @endfor
            </select>
            <button class="btn btn-secondary" type="submit"><i class="fa-solid fa-filter"></i></button>
            <a class="btn btn-success" href="{{ route('logistics.tools.carrier_end_of_day.checks.export', ['year' => request('check_year'), 'month' => request('check_month')]) }}"><i class="fa-solid fa-file-csv"></i></a>
        </form>
    </div>
    <div style="width:100%; overflow-x:auto;">
        <table class="table table-bordered customTable text-center" style="min-width:900px; margin-bottom:0;">
            <thead>
                <tr style="text-transform:uppercase;">
                    <th>Date</th>
                    <th>Carrier</th>
                    <th>Shipments</th>
                    <th>Checked</th>
                    <th>Non-standard</th>
                    <th>Note</th>
                    <th>User</th>
                </tr>
            </thead>
            <tbody>
                @forelse($checksHistory as $check)
                    <tr style="{{ $check->has_diff ? 'color:#c62828; font-weight:600;' : '' }}">
                        <td>{{ $check->check_date }}</td>
                        <td>{{ $check->carrier_name }}</td>
                        <td>{{ $check->shipments }}</td>
                        <td>{{ $check->qty_checked }}</td>
                        <td>{{ $check->non_standard }}</td>
                        <td style="text-align:left;">{{ $check->note ?: '-' }}</td>
                        <td>{{ $check->user_id }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">No carrier checks found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
